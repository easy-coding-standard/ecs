<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ECSPrefix202605\Symfony\Component\Console\Event;

use ECSPrefix202605\Symfony\Component\Console\Command\Command;
use ECSPrefix202605\Symfony\Component\Console\Input\InputInterface;
use ECSPrefix202605\Symfony\Component\Console\Output\OutputInterface;
/**
 * Allows to manipulate the exit code of a command after its execution.
 *
 * @author Francesco Levorato <git@flevour.net>
 * @author Jules Pietri <jules@heahprod.com>
 */
final class ConsoleTerminateEvent extends ConsoleEvent
{
    /**
     * @var int
     */
    private $exitCode;
    /**
     * @readonly
     * @var int|null
     */
    private $interruptingSignal;
    public function __construct(Command $command, InputInterface $input, OutputInterface $output, int $exitCode, ?int $interruptingSignal = null)
    {
        $this->exitCode = $exitCode;
        $this->interruptingSignal = $interruptingSignal;
        parent::__construct($command, $input, $output);
    }
    public function setExitCode(int $exitCode): void
    {
        $this->exitCode = $exitCode;
    }
    public function getExitCode(): int
    {
        return $this->exitCode;
    }
    public function getInterruptingSignal(): ?int
    {
        return $this->interruptingSignal;
    }
}
