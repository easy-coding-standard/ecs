<?php

namespace ECSPrefix202605\Illuminate\Contracts\Container;

use Exception;
use ECSPrefix202605\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
