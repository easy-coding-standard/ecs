<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Config\Level;

use PHP_CodeSniffer\Sniffs\Sniff;
use PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer;
use PhpCsFixer\Fixer\ClassNotation\SingleClassElementPerStatementFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Fixer\LanguageConstruct\ExplicitIndirectVariableFixer;
use PhpCsFixer\Fixer\LanguageConstruct\FunctionToConstantFixer;
use PhpCsFixer\Fixer\LanguageConstruct\IsNullFixer;
use PhpCsFixer\Fixer\Operator\NewWithBracesFixer;
use PhpCsFixer\Fixer\Operator\StandardizeIncrementFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitMethodCasingFixer;
use PhpCsFixer\Fixer\StringNotation\ExplicitStringVariableFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;

/**
 * Key 0 = level 0
 *
 * Start at 0, go slowly higher, one level per PR, and tighten your control flow / class structure.
 *
 * From the safest rules to more changing ones.
 */
final class ControlStructuresLevel
{
    /**
     * @var array<class-string<Sniff|FixerInterface>>
     */
    public const array RULES = [
        // pure casing / cleanup
        MagicConstantCasingFixer::class,
        SingleQuoteFixer::class,
        PhpUnitMethodCasingFixer::class,

        // safe single-token swaps
        IsNullFixer::class,
        FunctionToConstantFixer::class,
        StandardizeIncrementFixer::class,
        NewWithBracesFixer::class,

        // string and variable handling
        ExplicitStringVariableFixer::class,
        ExplicitIndirectVariableFixer::class,

        // class-level tweaks
        SelfAccessorFixer::class,
        ClassDefinitionFixer::class,
        SingleClassElementPerStatementFixer::class,

        // invasive control-flow / ordering changes
        YodaStyleFixer::class,
        NoUselessElseFixer::class,
        OrderedClassElementsFixer::class,
    ];

    /**
     * @var array<class-string<Sniff|FixerInterface>, mixed[]>
     */
    public const array RULE_CONFIGURATIONS = [
        SingleClassElementPerStatementFixer::class => [
            'elements' => ['const', 'property'],
        ],
        ClassDefinitionFixer::class => [
            'single_line' => true,
        ],
        YodaStyleFixer::class => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],
    ];
}
