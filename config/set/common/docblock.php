<?php

declare (strict_types=1);
namespace ECSPrefix202511;

use PhpCsFixer\Fixer\Comment\NoTrailingWhitespaceInCommentFixer;
use PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocIndentFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoEmptyReturnFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocReturnSelfReferenceFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimConsecutiveBlankLineSeparationFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocVarWithoutNameFixer;
use Symplify\CodingStandard\Fixer\Commenting\RemoveUselessDefaultCommentFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
return ECSConfig::configure()->withRules([PhpdocLineSpanFixer::class, NoTrailingWhitespaceInCommentFixer::class, PhpdocTrimConsecutiveBlankLineSeparationFixer::class, PhpdocTrimFixer::class, NoEmptyPhpdocFixer::class, PhpdocNoEmptyReturnFixer::class, PhpdocIndentFixer::class, PhpdocTypesFixer::class, PhpdocReturnSelfReferenceFixer::class, PhpdocVarWithoutNameFixer::class, RemoveUselessDefaultCommentFixer::class])->withConfiguredRule(NoSuperfluousPhpdocTagsFixer::class, ['remove_inheritdoc' => \true, 'allow_mixed' => \true]);
