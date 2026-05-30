<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ECSPrefix202605\Symfony\Contracts\Service;

use ECSPrefix202605\Psr\Container\ContainerInterface;
/**
 * Implemented by objects that expose a service container.
 */
interface ContainerAwareInterface
{
    public function getContainer(): ContainerInterface;
}
