<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Config\Level;

use PHP_CodeSniffer\Sniffs\Sniff;
use PhpCsFixer\Fixer\Comment\NoTrailingWhitespaceInCommentFixer;
use PhpCsFixer\Fixer\FixerInterface;
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

/**
 * Key 0 = level 0
 *
 * Start at 0, go slowly higher, one level per PR, and improve your docblock coverage.
 *
 * From the safest rules to more changing ones.
 */
final class DocblockLevel
{
    /**
     * @var array<class-string<Sniff|FixerInterface>>
     */
    public const array RULES = [
        // pure whitespace cleanup
        NoTrailingWhitespaceInCommentFixer::class,
        PhpdocTrimFixer::class,
        PhpdocTrimConsecutiveBlankLineSeparationFixer::class,
        PhpdocIndentFixer::class,

        // type / formatting normalization
        PhpdocTypesFixer::class,
        PhpdocLineSpanFixer::class,
        PhpdocVarWithoutNameFixer::class,
        PhpdocReturnSelfReferenceFixer::class,

        // dropping content
        NoEmptyPhpdocFixer::class,
        PhpdocNoEmptyReturnFixer::class,
        RemoveUselessDefaultCommentFixer::class,
        NoSuperfluousPhpdocTagsFixer::class,
    ];

    /**
     * @var array<class-string<Sniff|FixerInterface>, mixed[]>
     */
    public const array RULE_CONFIGURATIONS = [
        NoSuperfluousPhpdocTagsFixer::class => [
            'remove_inheritdoc' => true,
            'allow_mixed' => true,
        ],
    ];
}
