<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Console;

use Composer\XdebugHandler\XdebugHandler;
use Override;
use PHP_CodeSniffer\Config as PHP_CodeSniffer;
use PhpCsFixer\Console\Application as PhpCsFixer;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\EasyCodingStandard\Application\Version\StaticVersionResolver;
use Symplify\EasyCodingStandard\Console\Command\CheckCommand;
use Symplify\EasyCodingStandard\Console\Command\ListCheckersCommand;
use Symplify\EasyCodingStandard\Console\Command\ScriptsCommand;
use Symplify\EasyCodingStandard\Console\Command\WorkerCommand;
use Symplify\EasyCodingStandard\Console\Output\ConsoleOutputFormatter;
use Symplify\EasyCodingStandard\ValueObject\Option;

final class EasyCodingStandardConsoleApplication extends Application
{
    public function __construct(
        CheckCommand $checkCommand,
        WorkerCommand $workerCommand,
        ScriptsCommand $scriptsCommand,
        ListCheckersCommand $listCheckersCommand
    ) {
        parent::__construct('EasyCodingStandard', StaticVersionResolver::PACKAGE_VERSION);

        // used only internally, not needed to be public
        $workerCommand->setHidden();

        $this->add($checkCommand);
        $this->add($workerCommand);
        $this->add($scriptsCommand);
        $this->add($listCheckersCommand);

        $this->get('completion')
            ->setHidden();
        $this->get('help')
            ->setHidden();
        $this->get('list')
            ->setHidden();

        $this->setDefaultCommand('check');
    }

    #[Override]
    public function doRun(InputInterface $input, OutputInterface $output): int
    {
        // @fixes https://github.com/rectorphp/rector/issues/2205
        $isXdebugAllowed = $input->hasParameterOption('--xdebug');
        if (! $isXdebugAllowed && ! defined('PHPUNIT_COMPOSER_INSTALL')) {
            $xdebugHandler = new XdebugHandler('ecs');
            $xdebugHandler->check();
            unset($xdebugHandler);
        }

        // skip in this case, since generate content must be clear from meta-info
        if ($this->shouldPrintMetaInformation($input)) {
            $output->writeln($this->getLongVersion());
        }

        $exitCode = parent::doRun($input, $output);

        // Append to the output of --version
        if ($exitCode === 0 && $input->hasParameterOption(['--version', '-V'], true)) {
            $output->writeln(sprintf('+ %s <info>%s</info>', 'PHP_CodeSniffer', PHP_CodeSniffer::VERSION));
            $output->writeln(sprintf('+ %s <info>%s</info>', 'PHP-CS-Fixer', PhpCsFixer::VERSION));
        }

        return $exitCode;
    }

    #[Override]
    protected function getDefaultInputDefinition(): InputDefinition
    {
        $inputDefinition = parent::getDefaultInputDefinition();
        $this->addExtraOptions($inputDefinition);

        return $inputDefinition;
    }

    private function shouldPrintMetaInformation(InputInterface $input): bool
    {
        $hasNoArguments = $input->getFirstArgument() === null;

        if ($hasNoArguments) {
            return false;
        }

        $outputFormat = $input->getParameterOption('--' . Option::OUTPUT_FORMAT);

        return $outputFormat === ConsoleOutputFormatter::getName();
    }

    private function addExtraOptions(InputDefinition $inputDefinition): void
    {
        $inputDefinition->addOption(new InputOption(
            Option::XDEBUG,
            null,
            InputOption::VALUE_NONE,
            'Allow running xdebug'
        ));

        $inputDefinition->addOption(new InputOption(
            Option::DEBUG,
            null,
            InputOption::VALUE_NONE,
            'Run in debug mode (alias for "-vvv")'
        ));
    }
}
