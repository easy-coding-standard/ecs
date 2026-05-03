<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Config\Level;

use PHP_CodeSniffer\Sniffs\Sniff;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\TrimArraySpacesFixer;
use PhpCsFixer\Fixer\ArrayNotation\WhitespaceAfterCommaInArrayFixer;
use PhpCsFixer\Fixer\Basic\NoTrailingCommaInSinglelineFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\StandaloneLineInMultilineArrayFixer;

/**
 * Key 0 = level 0
 *
 * Start at 0, go slowly higher, one level per PR, and improve your array coverage.
 *
 * From the safest rules to more changing ones.
 */
final class ArrayLevel
{
    /**
     * @var array<class-string<Sniff|FixerInterface>>
     */
    public const array RULES = [
        // pure cleanup, no opinion on layout
        NoWhitespaceBeforeCommaInArrayFixer::class,
        WhitespaceAfterCommaInArrayFixer::class,
        TrimArraySpacesFixer::class,
        NoTrailingCommaInSinglelineFixer::class,

        // syntax normalization
        ArraySyntaxFixer::class,
        TrailingCommaInMultilineFixer::class,

        // invasive layout changes
        ArrayIndentationFixer::class,
        ArrayOpenerAndCloserNewlineFixer::class,
        ArrayListItemNewlineFixer::class,
        StandaloneLineInMultilineArrayFixer::class,
    ];

    /**
     * @var array<class-string<Sniff|FixerInterface>, mixed[]>
     */
    public const array RULE_CONFIGURATIONS = [
        NoTrailingCommaInSinglelineFixer::class => [
            'elements' => ['arguments', 'array_destructuring', 'array', 'group_import'],
        ],
        ArraySyntaxFixer::class => [
            'syntax' => 'short',
        ],
        TrailingCommaInMultilineFixer::class => [
            'elements' => [TrailingCommaInMultilineFixer::ELEMENTS_ARRAYS],
        ],
    ];
}
