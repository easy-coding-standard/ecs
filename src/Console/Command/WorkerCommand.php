<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Console\Command;

use ECSPrefix202605\Clue\React\NDJson\Decoder;
use ECSPrefix202605\Clue\React\NDJson\Encoder;
use Override;
use ECSPrefix202605\React\EventLoop\StreamSelectLoop;
use ECSPrefix202605\React\Socket\ConnectionInterface;
use ECSPrefix202605\React\Socket\TcpConnector;
use ECSPrefix202605\Symfony\Component\Console\Input\InputInterface;
use ECSPrefix202605\Symfony\Component\Console\Output\OutputInterface;
use Symplify\EasyCodingStandard\Configuration\ConfigurationFactory;
use Symplify\EasyCodingStandard\MemoryLimitter;
use Symplify\EasyCodingStandard\Parallel\WorkerRunner;
use ECSPrefix202605\Symplify\EasyParallel\Enum\Action;
use ECSPrefix202605\Symplify\EasyParallel\Enum\ReactCommand;
/**
 * Inspired at: https://github.com/phpstan/phpstan-src/commit/9124c66dcc55a222e21b1717ba5f60771f7dda92
 * https://github.com/phpstan/phpstan-src/blob/c471c7b050e0929daf432288770de673b394a983/src/Command/WorkerCommand.php
 *
 * ↓↓↓
 * https://github.com/phpstan/phpstan-src/commit/b84acd2e3eadf66189a64fdbc6dd18ff76323f67#diff-7f625777f1ce5384046df08abffd6c911cfbb1cfc8fcb2bdeaf78f337689e3e2
 */
final class WorkerCommand extends \Symplify\EasyCodingStandard\Console\Command\AbstractCheckCommand
{
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\Parallel\WorkerRunner
     */
    private $workerRunner;
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\MemoryLimitter
     */
    private $memoryLimitter;
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\Configuration\ConfigurationFactory
     */
    private $configurationFactory;
    public function __construct(WorkerRunner $workerRunner, MemoryLimitter $memoryLimitter, ConfigurationFactory $configurationFactory)
    {
        $this->workerRunner = $workerRunner;
        $this->memoryLimitter = $memoryLimitter;
        $this->configurationFactory = $configurationFactory;
        parent::__construct();
    }
    #[Override]
    protected function configure(): void
    {
        $this->setName('worker');
        $this->setDescription('[INTERNAL] Support for parallel process');
        parent::configure();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configuration = $this->configurationFactory->createFromInput($input);
        $this->memoryLimitter->adjust($configuration);
        $streamSelectLoop = new StreamSelectLoop();
        $parallelIdentifier = $configuration->getParallelIdentifier();
        $tcpConnector = new TcpConnector($streamSelectLoop);
        $promise = $tcpConnector->connect('127.0.0.1:' . $configuration->getParallelPort());
        $promise->then(function (ConnectionInterface $connection) use ($parallelIdentifier, $configuration): void {
            $inDecoder = new Decoder($connection, \true, 512, \JSON_INVALID_UTF8_IGNORE);
            $outEncoder = new Encoder($connection, \JSON_INVALID_UTF8_IGNORE);
            // handshake?
            $outEncoder->write([ReactCommand::ACTION => Action::HELLO, ReactCommand::IDENTIFIER => $parallelIdentifier]);
            $this->workerRunner->run($outEncoder, $inDecoder, $configuration);
        });
        $streamSelectLoop->run();
        return self::SUCCESS;
    }
}
