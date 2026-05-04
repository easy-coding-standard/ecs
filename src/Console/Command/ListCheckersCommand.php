<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Console\Command;

use ECSPrefix202605\Nette\Utils\Json;
use PHP_CodeSniffer\Sniffs\Sniff;
use PhpCsFixer\Fixer\FixerInterface;
use ECSPrefix202605\Symfony\Component\Console\Command\Command;
use ECSPrefix202605\Symfony\Component\Console\Input\InputInterface;
use ECSPrefix202605\Symfony\Component\Console\Input\InputOption;
use ECSPrefix202605\Symfony\Component\Console\Output\OutputInterface;
use Symplify\EasyCodingStandard\Console\Output\ConsoleOutputFormatter;
use Symplify\EasyCodingStandard\Console\Reporter\CheckerListReporter;
use Symplify\EasyCodingStandard\FixerRunner\Application\FixerFileProcessor;
use Symplify\EasyCodingStandard\Skipper\SkipCriteriaResolver\SkippedClassResolver;
use Symplify\EasyCodingStandard\SniffRunner\Application\SniffFileProcessor;
use Symplify\EasyCodingStandard\ValueObject\Option;
final class ListCheckersCommand extends Command
{
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\SniffRunner\Application\SniffFileProcessor
     */
    private $sniffFileProcessor;
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\FixerRunner\Application\FixerFileProcessor
     */
    private $fixerFileProcessor;
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\Console\Reporter\CheckerListReporter
     */
    private $checkerListReporter;
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\Skipper\SkipCriteriaResolver\SkippedClassResolver
     */
    private $skippedClassResolver;
    public function __construct(SniffFileProcessor $sniffFileProcessor, FixerFileProcessor $fixerFileProcessor, CheckerListReporter $checkerListReporter, SkippedClassResolver $skippedClassResolver)
    {
        $this->sniffFileProcessor = $sniffFileProcessor;
        $this->fixerFileProcessor = $fixerFileProcessor;
        $this->checkerListReporter = $checkerListReporter;
        $this->skippedClassResolver = $skippedClassResolver;
        parent::__construct();
    }
    protected function configure(): void
    {
        $this->setName('list-checkers');
        $this->setDescription('Shows loaded checkers');
        $this->addOption(Option::OUTPUT_FORMAT, null, InputOption::VALUE_REQUIRED, 'Select output format', ConsoleOutputFormatter::getName());
        $this->addOption(Option::CONFIG, 'c', InputOption::VALUE_REQUIRED, 'Path to config file');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputFormat = $input->getOption(Option::OUTPUT_FORMAT);
        // include skipped rules to avoid adding those too
        $skippedCheckers = $this->getSkippedCheckers();
        if ($outputFormat === 'json') {
            $data = ['sniffs' => $this->getSniffClasses(), 'fixers' => $this->getFixerClasses(), 'skipped-checkers' => $skippedCheckers];
            echo Json::encode($data, Json::PRETTY) . \PHP_EOL;
            return Command::SUCCESS;
        }
        $this->checkerListReporter->report($this->getSniffClasses(), 'from PHP_CodeSniffer');
        $this->checkerListReporter->report($this->getFixerClasses(), 'from PHP-CS-Fixer');
        $this->checkerListReporter->report($skippedCheckers, 'are skipped');
        return self::SUCCESS;
    }
    /**
     * @return array<class-string<FixerInterface>>
     */
    private function getFixerClasses(): array
    {
        $fixers = $this->fixerFileProcessor->getCheckers();
        return $this->getObjectClasses($fixers);
    }
    /**
     * @return array<class-string<Sniff>>
     */
    private function getSniffClasses(): array
    {
        $sniffs = $this->sniffFileProcessor->getCheckers();
        return $this->getObjectClasses($sniffs);
    }
    /**
     * @template TObject as Sniff|FixerInterface
     * @param TObject[] $checkers
     * @return array<class-string<TObject>>
     */
    private function getObjectClasses(array $checkers): array
    {
        $objectClasses = array_map(static function (object $fixer): string {
            return get_class($fixer);
        }, $checkers);
        sort($objectClasses);
        return $objectClasses;
    }
    /**
     * @return string[]
     */
    private function getSkippedCheckers(): array
    {
        $skippedCheckers = [];
        foreach ($this->skippedClassResolver->resolve() as $checkerClass => $fileList) {
            // ignore specific skips
            if ($fileList !== null) {
                continue;
            }
            $skippedCheckers[] = $checkerClass;
        }
        return $skippedCheckers;
    }
}
