<?php

declare (strict_types=1);
namespace ECSPrefix202605;

use Symplify\EasyCodingStandard\Config\ECSConfig;
return ECSConfig::configure()->withPaths([__DIR__ . '/config', __DIR__ . '/src', __DIR__ . '/tests'])->withRootFiles()->withPreparedSets(\true, \true);
