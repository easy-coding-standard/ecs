<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Application;

use Symplify\EasyCodingStandard\Caching\ChangedFilesDetector;
use Symplify\EasyCodingStandard\Skipper\Skipper\Skipper;
use Symplify\EasyCodingStandard\SniffRunner\ValueObject\Error\CodingStandardError;
use Symplify\EasyCodingStandard\ValueObject\Configuration;
use Symplify\EasyCodingStandard\ValueObject\Error\FileDiff;
final class SingleFileProcessor
{
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\Skipper\Skipper\Skipper
     */
    private $skipper;
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\Caching\ChangedFilesDetector
     */
    private $changedFilesDetector;
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\Application\FileProcessorCollector
     */
    private $fileProcessorCollector;
    public function __construct(Skipper $skipper, ChangedFilesDetector $changedFilesDetector, \Symplify\EasyCodingStandard\Application\FileProcessorCollector $fileProcessorCollector)
    {
        $this->skipper = $skipper;
        $this->changedFilesDetector = $changedFilesDetector;
        $this->fileProcessorCollector = $fileProcessorCollector;
    }
    /**
     * @return array{file_diffs?: FileDiff[], coding_standard_errors?: CodingStandardError[]}
     */
    public function processFilePath(string $filePath, Configuration $configuration) : array
    {
        if ($this->skipper->shouldSkipFilePath($filePath)) {
            return [];
        }
        $errorsAndDiffs = [];
        $this->changedFilesDetector->addFilePath($filePath);
        $fileProcessors = $this->fileProcessorCollector->getFileProcessors();
        foreach ($fileProcessors as $fileProcessor) {
            if ($fileProcessor->getCheckers() === []) {
                continue;
            }
            $currentErrorsAndFileDiffs = $fileProcessor->processFile($filePath, $configuration);
            if ($currentErrorsAndFileDiffs === []) {
                continue;
            }
            $errorsAndDiffs = \array_merge($errorsAndDiffs, $currentErrorsAndFileDiffs);
        }
        // invalidate broken file, to analyse in next run too
        if ($errorsAndDiffs !== []) {
            $this->changedFilesDetector->invalidateFilePath($filePath);
        }
        return $errorsAndDiffs;
    }
}
