<?php

declare (strict_types=1);
namespace ECSPrefix202605\Entropy\Console\Output;

use ECSPrefix202605\Entropy\Console\CommandRegistry;
final class HelpPrinter
{
    /**
     * @readonly
     * @var \Entropy\Console\CommandRegistry
     */
    private $commandRegistry;
    /**
     * @readonly
     * @var \Entropy\Console\Output\OutputPrinter
     */
    private $outputPrinter;
    /**
     * @var int
     */
    private const MIN_WIDTH = 10;
    public function __construct(CommandRegistry $commandRegistry, OutputPrinter $outputPrinter)
    {
        $this->commandRegistry = $commandRegistry;
        $this->outputPrinter = $outputPrinter;
    }
    public function print(): void
    {
        $this->outputPrinter->yellow('Commands:');
        $maxCommandNameLength = $this->commandRegistry->getCommandNameMaxLength();
        $firstColumnWith = max(self::MIN_WIDTH, $maxCommandNameLength) + 3;
        foreach ($this->commandRegistry->all() as $command) {
            $commandName = str_pad($command->getName(), $firstColumnWith);
            $this->outputPrinter->writeln(sprintf('  <fg=green>%s</>  %s', $commandName, $command->getDescription()));
        }
        $this->outputPrinter->newline();
        $this->outputPrinter->yellow('Options:');
        $optionName = str_pad('--help, -h', $firstColumnWith);
        $this->outputPrinter->writeln(sprintf('  <fg=green>%s</>  Show this help', $optionName));
    }
}
