<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Console\Style;

use ECSPrefix202605\Symfony\Component\Console\Input\ArgvInput;
use ECSPrefix202605\Symfony\Component\Console\Input\InputInterface;
use ECSPrefix202605\Symfony\Component\Console\Output\ConsoleOutput;
use ECSPrefix202605\Symfony\Component\Console\Output\OutputInterface;
use ECSPrefix202605\Symfony\Component\Console\Style\SymfonyStyle;
/**
 * @api
 */
final class SymfonyStyleFactory
{
    public static function create() : SymfonyStyle
    {
        // to prevent missing argv indexes
        if (!isset($_SERVER['argv'])) {
            $_SERVER['argv'] = [];
        }
        $argvInput = new ArgvInput();
        $consoleOutput = new ConsoleOutput();
        self::applySymfonyConsoleArgs($argvInput, $consoleOutput);
        // --debug is called
        if ($argvInput->hasParameterOption('--debug')) {
            $consoleOutput->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        }
        // disable output for tests
        if (self::isPHPUnitRun()) {
            $consoleOutput->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }
        return new SymfonyStyle($argvInput, $consoleOutput);
    }
    /**
     * Never ever used static methods if not neccesary, this is just handy for tests + src to prevent duplication.
     */
    private static function isPHPUnitRun() : bool
    {
        return \defined('PHPUNIT_COMPOSER_INSTALL') || \defined('__PHPUNIT_PHAR__');
    }
    /**
     * This method was derived from the `Application::configureIO` method of the Symfony Console
     * component, under the MIT license. See NOTICE for the full license.
     *
     * @see https://github.com/symfony/console/blob/0aa29ca177f432ab68533432db0de059f39c92ae/Application.php#L905
     */
    private static function applySymfonyConsoleArgs(InputInterface $input, OutputInterface $output) : void
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
