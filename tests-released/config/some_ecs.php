<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

return (ECSConfig::configure())
    ->withSets([
        // PHP 7.2+ compatible config, to enable PHP 7.2 tests
        \Symplify\EasyCodingStandard\ValueObject\Set\SetList::PSR_12
    ]);
