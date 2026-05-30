<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ECSPrefix202605\Symfony\Contracts\Service\Test;

use PHPUnit\Framework\TestCase;
use ECSPrefix202605\Psr\Container\ContainerExceptionInterface;
use ECSPrefix202605\Psr\Container\ContainerInterface;
use ECSPrefix202605\Psr\Container\NotFoundExceptionInterface;
use ECSPrefix202605\Symfony\Contracts\Service\ServiceLocatorTrait;
abstract class ServiceLocatorTestCase extends TestCase
{
    /**
     * @param array<string, callable> $factories
     */
    protected function getServiceLocator(array $factories): ContainerInterface
    {
        return new class($factories) implements ContainerInterface
        {
            use ServiceLocatorTrait;
        };
    }
    public function testHas()
    {
        $locator = $this->getServiceLocator(['foo' => static function () {
            return 'bar';
        }, 'bar' => static function () {
            return 'baz';
        }, static function () {
            return 'dummy';
        }]);
        $this->assertTrue($locator->has('foo'));
        $this->assertTrue($locator->has('bar'));
        $this->assertFalse($locator->has('dummy'));
    }
    public function testGet()
    {
        $locator = $this->getServiceLocator(['foo' => static function () {
            return 'bar';
        }, 'bar' => static function () {
            return 'baz';
        }]);
        $this->assertSame('bar', $locator->get('foo'));
        $this->assertSame('baz', $locator->get('bar'));
    }
    public function testGetDoesNotMemoize()
    {
        $i = 0;
        $locator = $this->getServiceLocator(['foo' => static function () use (&$i) {
            ++$i;
            return 'bar';
        }]);
        $this->assertSame('bar', $locator->get('foo'));
        $this->assertSame('bar', $locator->get('foo'));
        $this->assertSame(2, $i);
    }
    public function testThrowsOnUndefinedInternalService()
    {
        $locator = $this->getServiceLocator(['foo' => static function () use (&$locator) {
            return $locator->get('bar');
        }]);
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('The service "foo" has a dependency on a non-existent service "bar". This locator only knows about the "foo" service.');
        $locator->get('foo');
    }
    public function testThrowsOnCircularReference()
    {
        $locator = $this->getServiceLocator(['foo' => static function () use (&$locator) {
            return $locator->get('bar');
        }, 'bar' => static function () use (&$locator) {
            return $locator->get('baz');
        }, 'baz' => static function () use (&$locator) {
            return $locator->get('bar');
        }]);
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectExceptionMessage('Circular reference detected for service "bar", path: "bar -> baz -> bar".');
        $locator->get('foo');
    }
}
