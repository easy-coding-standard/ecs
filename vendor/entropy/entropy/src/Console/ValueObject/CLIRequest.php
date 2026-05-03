<?php

declare (strict_types=1);
namespace ECSPrefix202605\Entropy\Console\ValueObject;

use ECSPrefix202605\Webmozart\Assert\Assert;
final class CLIRequest
{
    /**
     * @readonly
     * @var string|null
     */
    private $commandName;
    /**
     * @var mixed[]
     * @readonly
     */
    private $arguments = [];
    /**
     * @var array<string, mixed>
     */
    private $options = [];
    /**
     * @param mixed[] $arguments
     * @param array<string, mixed> $options
     */
    public function __construct(?string $commandName, array $arguments = [], array $options = [])
    {
        $this->commandName = $commandName;
        $this->arguments = $arguments;
        $this->options = $options;
        Assert::allString(\array_keys($options));
    }
    public function getCommandName() : ?string
    {
        return $this->commandName;
    }
    /**
     * @return mixed[]
     */
    public function getArguments() : array
    {
        return $this->arguments;
    }
    /**
     * @return array<string, mixed>
     */
    public function getOptions() : array
    {
        return $this->options;
    }
    /**
     * @param mixed $default
     * @return mixed
     */
    public function option(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }
    public function isHelp() : bool
    {
        return $this->commandName === null;
    }
    public function isCommandHelp() : bool
    {
        if ($this->commandName === null) {
            return \false;
        }
        return \array_intersect(['h', 'help'], \array_keys($this->options)) !== [];
    }
}
