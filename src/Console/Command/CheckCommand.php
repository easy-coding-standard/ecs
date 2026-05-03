<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Console\Command;

use ECSPrefix202605\Override;
use ECSPrefix202605\Symfony\Component\Console\Input\InputInterface;
use ECSPrefix202605\Symfony\Component\Console\Output\OutputInterface;
use Symplify\EasyCodingStandard\Application\EasyCodingStandardApplication;
use Symplify\EasyCodingStandard\Configuration\ConfigInitializer;
use Symplify\EasyCodingStandard\Configuration\ConfigurationFactory;
use Symplify\EasyCodingStandard\MemoryLimitter;
use Symplify\EasyCodingStandard\Reporter\ProcessedFileReporter;
final class CheckCommand extends \Symplify\EasyCodingStandard\Console\Command\AbstractCheckCommand
{
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\Reporter\ProcessedFileReporter
     */
    private $processedFileReporter;
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\MemoryLimitter
     */
    private $memoryLimitter;
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\Configuration\ConfigInitializer
     */
    private $configInitializer;
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\Application\EasyCodingStandardApplication
     */
    private $easyCodingStandardApplication;
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\Configuration\ConfigurationFactory
     */
    private $configurationFactory;
    public function __construct(ProcessedFileReporter $processedFileReporter, MemoryLimitter $memoryLimitter, ConfigInitializer $configInitializer, EasyCodingStandardApplication $easyCodingStandardApplication, ConfigurationFactory $configurationFactory)
    {
        $this->processedFileReporter = $processedFileReporter;
        $this->memoryLimitter = $memoryLimitter;
        $this->configInitializer = $configInitializer;
        $this->easyCodingStandardApplication = $easyCodingStandardApplication;
        $this->configurationFactory = $configurationFactory;
        parent::__construct();
    }
    #[Override]
    protected function configure() : void
    {
        $this->setName('check');
        $this->setDescription('Check coding standard in one or more directories');
        parent::configure();
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        // create ecs.php config file if does not exist yet
        if (!$this->configInitializer->areSomeCheckersRegistered()) {
            $this->configInitializer->createConfig(\getcwd());
            return self::SUCCESS;
        }
        $configuration = $this->configurationFactory->createFromInput($input);
        $this->memoryLimitter->adjust($configuration);
        $errorsAndDiffs = $this->easyCodingStandardApplication->run($configuration, $input);
        return $this->processedFileReporter->report($errorsAndDiffs, $configuration);
    }
}
