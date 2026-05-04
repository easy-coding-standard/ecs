<?php

declare (strict_types=1);
namespace ECSPrefix202605\Entropy\Console\ValueObject;

final class Argument
{
    /**
     * @readonly
     * @var string
     */
    private $name;
    /**
     * @readonly
     * @var string|null
     */
    private $description;
    /**
     * @readonly
     * @var bool
     */
    private $acceptsMultipleValues = \false;
    public function __construct(string $name, ?string $description = null, bool $acceptsMultipleValues = \false)
    {
        $this->name = $name;
        $this->description = $description;
        $this->acceptsMultipleValues = $acceptsMultipleValues;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function doesAcceptMultipleValues(): bool
    {
        return $this->acceptsMultipleValues;
    }
}
