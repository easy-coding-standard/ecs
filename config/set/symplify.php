<?php

declare (strict_types=1);
namespace ECSPrefix202605;

use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use Symplify\CodingStandard\Fixer\Annotation\RemoveMethodNameDuplicateDescriptionFixer;
use Symplify\CodingStandard\Fixer\Annotation\RemovePHPStormAnnotationFixer;
use Symplify\CodingStandard\Fixer\Annotation\RemovePropertyVariableNameDescriptionFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\CodingStandard\Fixer\Commenting\ParamReturnAndVarTagMalformsFixer;
use Symplify\CodingStandard\Fixer\Commenting\RemoveUselessDefaultCommentFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\CodingStandard\Fixer\Spacing\MethodChainingNewlineFixer;
use Symplify\CodingStandard\Fixer\Spacing\SpaceAfterCommaHereNowDocFixer;
use Symplify\CodingStandard\Fixer\Spacing\StandaloneLinePromotedPropertyFixer;
use Symplify\CodingStandard\Fixer\Strict\BlankLineAfterStrictTypesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
return ECSConfig::configure()->withRules([
    // docblocks and comments
    RemovePHPStormAnnotationFixer::class,
    ParamReturnAndVarTagMalformsFixer::class,
    RemoveUselessDefaultCommentFixer::class,
    RemoveMethodNameDuplicateDescriptionFixer::class,
    RemovePropertyVariableNameDescriptionFixer::class,
    // arrays
    ArrayListItemNewlineFixer::class,
    ArrayOpenerAndCloserNewlineFixer::class,
    StandaloneLinePromotedPropertyFixer::class,
    // newlines
    MethodChainingNewlineFixer::class,
    SpaceAfterCommaHereNowDocFixer::class,
    BlankLineAfterStrictTypesFixer::class,
    LineLengthFixer::class,
])->withConfiguredRule(GeneralPhpdocAnnotationRemoveFixer::class, ['annotations' => ['throws', 'author', 'package', 'group', 'covers', 'category']]);
