<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ECSPrefix202511\Symfony\Component\Console\Event;

use ECSPrefix202511\Symfony\Component\Console\Command\Command;
use ECSPrefix202511\Symfony\Component\Console\Input\InputInterface;
use ECSPrefix202511\Symfony\Component\Console\Output\OutputInterface;
/**
 * @author marie <marie@users.noreply.github.com>
 */
final class ConsoleSignalEvent extends ConsoleEvent
{
    /**
     * @var int
     */
    private $handlingSignal;
    /**
     * @var int|false
     */
    private $exitCode;
    /**
     * @param int|false $exitCode
     */
    public function __construct(Command $command, InputInterface $input, OutputInterface $output, int $handlingSignal, $exitCode = 0)
    {
        parent::__construct($command, $input, $output);
        $this->handlingSignal = $handlingSignal;
        $this->exitCode = $exitCode;
    }
    public function getHandlingSignal() : int
    {
        return $this->handlingSignal;
    }
    public function setExitCode(int $exitCode) : void
    {
        if ($exitCode < 0 || $exitCode > 255) {
            throw new \InvalidArgumentException('Exit code must be between 0 and 255.');
        }
        $this->exitCode = $exitCode;
    }
    public function abortExit() : void
    {
        $this->exitCode = \false;
    }
    /**
     * @return int|false
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }
}
