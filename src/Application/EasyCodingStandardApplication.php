<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Application;

use ParseError;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\EasyCodingStandard\Caching\ChangedFilesDetector;
use Symplify\EasyCodingStandard\Console\Style\EasyCodingStandardStyle;
use Symplify\EasyCodingStandard\DependencyInjection\SimpleParameterProvider;
use Symplify\EasyCodingStandard\Exception\ShouldNotHappenException;
use Symplify\EasyCodingStandard\FileSystem\FileFilter;
use Symplify\EasyCodingStandard\FileSystem\StaticRelativeFilePathHelper;
use Symplify\EasyCodingStandard\Finder\SourceFinder;
use Symplify\EasyCodingStandard\Parallel\Application\ParallelFileProcessor;
use Symplify\EasyCodingStandard\Parallel\ValueObject\Bridge;
use Symplify\EasyCodingStandard\SniffRunner\ValueObject\Error\CodingStandardError;
use Symplify\EasyCodingStandard\Utils\ParametersMerger;
use Symplify\EasyCodingStandard\ValueObject\Configuration;
use Symplify\EasyCodingStandard\ValueObject\Error\FileDiff;
use Symplify\EasyCodingStandard\ValueObject\Error\SystemError;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyParallel\CpuCoreCountProvider;
use Symplify\EasyParallel\ScheduleFactory;

final readonly class EasyCodingStandardApplication
{
    private const string ARGV = 'argv';

    public function __construct(
        private EasyCodingStandardStyle $easyCodingStandardStyle,
        private SourceFinder $sourceFinder,
        private ChangedFilesDetector $changedFilesDetector,
        private FileFilter $fileFilter,
        private SingleFileProcessor $singleFileProcessor,
        private ScheduleFactory $scheduleFactory,
        private ParallelFileProcessor $parallelFileProcessor,
        private CpuCoreCountProvider $cpuCoreCountProvider,
        private SymfonyStyle $symfonyStyle,
        private ParametersMerger $parametersMerger
    ) {
    }

    /**
     * @return array{coding_standard_errors?: CodingStandardError[], file_diffs?: FileDiff[], system_errors?: SystemError[]|string[], system_errors_count?: int}
     */
    public function run(Configuration $configuration, InputInterface $input): array
    {
        // 1. find files in sources
        $filePaths = $this->sourceFinder->find($configuration->getSources());

        // 2. clear cache
        if ($configuration->shouldClearCache()) {
            $this->changedFilesDetector->clearCache();
        } else {
            $filePaths = $this->fileFilter->filterOnlyChangedFiles($filePaths);
        }

        // no files found
        $filesCount = count($filePaths);

        if ($filesCount === 0) {
            return [];
        }

        if ($configuration->isParallel()) {
            $schedule = $this->scheduleFactory->create(
                $this->cpuCoreCountProvider->provide(),
                SimpleParameterProvider::getIntParameter(Option::PARALLEL_JOB_SIZE),
                SimpleParameterProvider::getIntParameter(Option::PARALLEL_MAX_NUMBER_OF_PROCESSES),
                $filePaths
            );

            // for progress bar
            $isProgressBarStarted = false;

            $postFileCallback = function (int $stepCount) use (
                &$isProgressBarStarted,
                $filePaths,
                $configuration
            ): void {
                if (! $configuration->shouldShowProgressBar()) {
                    return;
                }

                if (! $isProgressBarStarted) {
                    $fileCount = count($filePaths);
                    $this->symfonyStyle->progressStart($fileCount);
                    $isProgressBarStarted = true;
                }

                $this->symfonyStyle->progressAdvance($stepCount);
                // running in parallel here → nothing else to do
            };

            $mainScript = $this->resolveCalledEcsBinary();
            if ($mainScript === null) {
                throw new ShouldNotHappenException('[parallel] Main script was not found');
            }

            // mimics see https://github.com/phpstan/phpstan-src/commit/9124c66dcc55a222e21b1717ba5f60771f7dda92#diff-387b8f04e0db7a06678eb52ce0c0d0aff73e0d7d8fc5df834d0a5fbec198e5daR139
            return $this->parallelFileProcessor->check(
                $schedule,
                $mainScript,
                $postFileCallback,
                $configuration->getConfig(),
                $input
            );
        }

        // process found files by each processors
        return $this->processFoundFiles($filePaths, $configuration);
    }

    /**
     * @param string[] $filePaths
     * @return array{coding_standard_errors: CodingStandardError[], file_diffs: FileDiff[], system_errors: SystemError[], system_errors_count: int}
     */
    private function processFoundFiles(array $filePaths, Configuration $configuration): array
    {
        $fileCount = count($filePaths);

        // 3. start progress bar
        $this->outputProgressBarAndDebugInfo($fileCount, $configuration);

        $errorsAndDiffs = [];

        foreach ($filePaths as $filePath) {
            if ($this->easyCodingStandardStyle->isDebug()) {
                $relativeFilePath = StaticRelativeFilePathHelper::resolveFromCwd($filePath);
                $this->easyCodingStandardStyle->writeln(' [file] ' . $relativeFilePath);
            }

            try {
                $currentErrorsAndDiffs = $this->singleFileProcessor->processFilePath($filePath, $configuration);
                if ($currentErrorsAndDiffs !== []) {
                    $errorsAndDiffs = $this->parametersMerger->merge($errorsAndDiffs, $currentErrorsAndDiffs);
                }
            } catch (ParseError $parseError) {
                $this->changedFilesDetector->invalidateFilePath($filePath);

                $relativeFilePath = StaticRelativeFilePathHelper::resolveFromCwd($filePath);

                $errorsAndDiffs[Bridge::SYSTEM_ERRORS][] = new SystemError(
                    $parseError->getLine(),
                    $parseError->getMessage(),
                    $relativeFilePath
                );
            }

            if ($configuration->shouldShowProgressBar()) {
                $this->easyCodingStandardStyle->progressAdvance();
            }
        }

        return $errorsAndDiffs;
    }

    private function outputProgressBarAndDebugInfo(int $fileInfoCount, Configuration $configuration): void
    {
        if (! $configuration->shouldShowProgressBar()) {
            return;
        }

        $this->easyCodingStandardStyle->progressStart($fileInfoCount);
    }

    /**
     * Path to called "ecs" binary file, e.g. "vendor/bin/ecs" returns "vendor/bin/ecs" This is needed to re-call the
     * ecs binary in sub-process in the same location.
     */
    private function resolveCalledEcsBinary(): ?string
    {
        if (! isset($_SERVER[self::ARGV][0])) {
            return null;
        }

        $potentialEcsBinaryPath = $_SERVER[self::ARGV][0];
        if (! file_exists($potentialEcsBinaryPath)) {
            return null;
        }

        return $potentialEcsBinaryPath;
    }
}
