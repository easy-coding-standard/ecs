<?php

declare (strict_types=1);
/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace PhpCsFixer\Runner\Parallel;

use PhpCsFixer\Runner\RunnerConfig;
use ECSPrefix202605\React\EventLoop\LoopInterface;
use ECSPrefix202605\Symfony\Component\Console\Input\InputInterface;
use ECSPrefix202605\Symfony\Component\Process\PhpExecutableFinder;
/**
 * @author Greg Korba <greg@codito.dev>
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ProcessFactory
{
    public function create(LoopInterface $loop, InputInterface $input, RunnerConfig $runnerConfig, \PhpCsFixer\Runner\Parallel\ProcessIdentifier $identifier, int $serverPort) : \PhpCsFixer\Runner\Parallel\Process
    {
        $commandArgs = $this->getCommandArgs($serverPort, $identifier, $input, $runnerConfig);
        return new \PhpCsFixer\Runner\Parallel\Process(\implode(' ', $commandArgs), $loop, $runnerConfig->getParallelConfig()->getProcessTimeout());
    }
    /**
     * @private
     *
     * @return non-empty-list<string>
     */
    public function getCommandArgs(int $serverPort, \PhpCsFixer\Runner\Parallel\ProcessIdentifier $identifier, InputInterface $input, RunnerConfig $runnerConfig) : array
    {
        $phpBinary = (new PhpExecutableFinder())->find(\false);
        if (\false === $phpBinary) {
            throw new \PhpCsFixer\Runner\Parallel\ParallelisationException('Cannot find PHP executable.');
        }
        $mainScript = \realpath(__DIR__ . '/../../../php-cs-fixer');
        if (\false === $mainScript && isset($_SERVER['argv'][0]) && \strpos($_SERVER['argv'][0], 'php-cs-fixer') !== \false) {
            $mainScript = $_SERVER['argv'][0];
        }
        if (!\is_file($mainScript)) {
            throw new \PhpCsFixer\Runner\Parallel\ParallelisationException('Cannot determine Fixer executable.');
        }
        $commandArgs = [\PhpCsFixer\Runner\Parallel\ProcessUtils::escapeArgument($phpBinary), \PhpCsFixer\Runner\Parallel\ProcessUtils::escapeArgument($mainScript), 'worker', \sprintf('--port=%s', (string) $serverPort), \sprintf('--identifier=%s', \PhpCsFixer\Runner\Parallel\ProcessUtils::escapeArgument($identifier->toString()))];
        if ($runnerConfig->isDryRun()) {
            $commandArgs[] = '--dry-run';
        }
        if (\filter_var($input->getOption('diff'), \FILTER_VALIDATE_BOOLEAN)) {
            $commandArgs[] = '--diff';
        }
        if (\filter_var($input->getOption('stop-on-violation'), \FILTER_VALIDATE_BOOLEAN)) {
            $commandArgs[] = '--stop-on-violation';
        }
        foreach (['allow-risky', 'config', 'rules', 'using-cache', 'cache-file'] as $option) {
            $optionValue = $input->getOption($option);
            if (null !== $optionValue) {
                $commandArgs[] = \sprintf('--%s=%s', $option, \PhpCsFixer\Runner\Parallel\ProcessUtils::escapeArgument($optionValue));
            }
        }
        return $commandArgs;
    }
}
