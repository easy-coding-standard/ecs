<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withRules([StrictComparisonFixer::class, StrictParamFixer::class, DeclareStrictTypesFixer::class]);
