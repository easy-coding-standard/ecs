<?php

namespace ECSPrefix202511\Illuminate\Contracts\Container;

use Exception;
use ECSPrefix202511\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
