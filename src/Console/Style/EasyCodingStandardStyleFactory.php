<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Console\Style;

use ECSPrefix202601\Symfony\Component\Console\Input\ArgvInput;
use ECSPrefix202601\Symfony\Component\Console\Input\InputInterface;
use ECSPrefix202601\Symfony\Component\Console\Output\ConsoleOutput;
use ECSPrefix202601\Symfony\Component\Console\Output\OutputInterface;
use ECSPrefix202601\Symfony\Component\Console\Terminal;
/**
 * @api
 */
final class EasyCodingStandardStyleFactory
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Terminal
     */
    private $terminal;
    public function __construct(Terminal $terminal)
    {
        $this->terminal = $terminal;
    }
    /**
     * @api
     */
    public function create() : \Symplify\EasyCodingStandard\Console\Style\EasyCodingStandardStyle
    {
        $argvInput = new ArgvInput();
        $consoleOutput = new ConsoleOutput();
        $this->applySymfonyConsoleArgs($argvInput, $consoleOutput);
        // --debug is called
        if ($argvInput->hasParameterOption('--debug')) {
            $consoleOutput->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        }
        // disable output for tests
        if (\defined('PHPUNIT_COMPOSER_INSTALL')) {
            $consoleOutput->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }
        return new \Symplify\EasyCodingStandard\Console\Style\EasyCodingStandardStyle($argvInput, $consoleOutput, $this->terminal);
    }
    /**
     * This method was derived from the `Application::configureIO` method of the Symfony Console
     * component, under the MIT license. See NOTICE for the full license.
     *
     * @see https://github.com/symfony/console/blob/0aa29ca177f432ab68533432db0de059f39c92ae/Application.php#L905
     */
    private function applySymfonyConsoleArgs(InputInterface $input, OutputInterface $output) : void
    {
        $enableAnsi = $input->hasParameterOption(['--ansi'], \true);
        $disableAnsi = $input->hasParameterOption(['--no-ansi'], \true);
        switch (\true) {
            case $enableAnsi:
                $output->setDecorated(\true);
                break;
            case $disableAnsi:
                $output->setDecorated(\false);
                break;
            default:
                null;
                break;
        }
        $enableQuiet = $input->hasParameterOption(['--quiet', '-q'], \true);
        $isVVV = $input->hasParameterOption('-vvv', \true);
        $isVV = $input->hasParameterOption('-vv', \true);
        $isV = $input->hasParameterOption('-v', \true);
        switch (\true) {
            case $enableQuiet:
                $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
                break;
            case $isVVV:
                $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
                break;
            case $isVV:
                $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
                break;
            case $isV:
                $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
                break;
            default:
                null;
                break;
        }
    }
}
