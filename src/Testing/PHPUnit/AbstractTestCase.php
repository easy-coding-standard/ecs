<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Testing\PHPUnit;

use ECSPrefix202605\Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use Symplify\EasyCodingStandard\DependencyInjection\LazyContainerFactory;
use ECSPrefix202605\Webmozart\Assert\Assert;
abstract class AbstractTestCase extends TestCase
{
    /**
     * @var \Illuminate\Container\Container|null
     */
    private $container;
    protected function setUp(): void
    {
        $lazyContainerFactory = new LazyContainerFactory();
        $this->container = $lazyContainerFactory->create();
        $this->container->boot();
    }
    /**
     * @param string[] $configs
     */
    protected function createContainerWithConfigs(array $configs): void
    {
        Assert::allString($configs);
        Assert::allFile($configs);
        $lazyContainerFactory = new LazyContainerFactory();
        $this->container = $lazyContainerFactory->create($configs);
        $this->container->boot();
    }
    /**
     * @template TObject as object
     *
     * @param class-string<TObject> $class
     * @return TObject
     */
    protected function make(string $class): object
    {
        Assert::notNull($this->container);
        return $this->container->make($class);
    }
}
