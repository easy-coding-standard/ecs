<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Console\Output;

use Symplify\EasyCodingStandard\Contract\Console\Output\OutputFormatterInterface;
use Symplify\EasyCodingStandard\Exception\Configuration\OutputFormatterNotFoundException;
final class OutputFormatterCollector
{
    /**
     * @var array<string, OutputFormatterInterface>
     */
    private $outputFormatters = [];
    /**
     * @param OutputFormatterInterface[] $outputFormatters
     */
    public function __construct(array $outputFormatters)
    {
        foreach ($outputFormatters as $outputFormatter) {
            $this->outputFormatters[$outputFormatter->getName()] = $outputFormatter;
        }
    }
    public function getByName(string $name) : OutputFormatterInterface
    {
        if (isset($this->outputFormatters[$name])) {
            return $this->outputFormatters[$name];
        }
        $outputFormatterKeys = \array_keys($this->outputFormatters);
        $errorMessage = \sprintf('Output formatter "%s" not found. Use one of: "%s".', $name, \implode('", "', $outputFormatterKeys));
        throw new OutputFormatterNotFoundException($errorMessage);
    }
}
