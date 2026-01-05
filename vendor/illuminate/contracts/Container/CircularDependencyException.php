<?php

namespace ECSPrefix202601\Illuminate\Contracts\Container;

use Exception;
use ECSPrefix202601\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
