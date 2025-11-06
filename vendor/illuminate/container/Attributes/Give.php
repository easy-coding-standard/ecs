<?php

namespace ECSPrefix202511\Illuminate\Container\Attributes;

use Attribute;
use ECSPrefix202511\Illuminate\Contracts\Container\Container;
use ECSPrefix202511\Illuminate\Contracts\Container\ContextualAttribute;
#[Attribute(Attribute::TARGET_PARAMETER)]
class Give implements ContextualAttribute
{
    /**
     * @var class-string<T>
     */
    public $class;
    /**
     * @var array|null
     */
    public $params = [];
    /**
     * Provide a concrete class implementation for dependency injection.
     *
     * @template T
     *
     * @param  class-string<T>  $class
     * @param  array|null  $params
     */
    public function __construct(string $class, array $params = [])
    {
        $this->class = $class;
        $this->params = $params;
    }
    /**
     * Resolve the dependency.
     *
     * @param  self  $attribute
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return mixed
     */
    public static function resolve(self $attribute, Container $container)
    {
        return $container->make($attribute->class, $attribute->params);
    }
}
