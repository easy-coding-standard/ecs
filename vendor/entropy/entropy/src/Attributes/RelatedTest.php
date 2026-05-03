<?php

declare (strict_types=1);
namespace ECSPrefix202605\Entropy\Attributes;

use Attribute;
use ECSPrefix202605\PHPUnit\Framework\TestCase;
#[Attribute(Attribute::TARGET_CLASS)]
final class RelatedTest
{
    /**
     * @param class-string<TestCase> $testClass
     */
    public function __construct(string $testClass)
    {
    }
}
