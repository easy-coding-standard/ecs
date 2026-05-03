<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ConstFetch\RemovePhpVersionIdCheckRector;

return RectorConfig::configure()
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        naming: true,
        earlyReturn: true
    )
    ->withPaths([__DIR__ . '/bin', __DIR__ . '/config', __DIR__ . '/src',  __DIR__ . '/tests'])
    ->withRootFiles()
    ->withImportNames()
    ->withBootstrapFiles([__DIR__ . '/tests/bootstrap.php'])
    ->withSkip([
        '*/Source/*',
        '*/Fixture/*',
        __DIR__ . '/src/SniffRunner/ValueObject/File.php',
        __DIR__ . '/scoper.php',

        // conditional checks
        RemovePhpVersionIdCheckRector::class,
    ]);
