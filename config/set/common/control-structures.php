<?php

declare (strict_types=1);
namespace ECSPrefix202601;

use PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer;
use PhpCsFixer\Fixer\ClassNotation\SingleClassElementPerStatementFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\LanguageConstruct\ExplicitIndirectVariableFixer;
use PhpCsFixer\Fixer\LanguageConstruct\FunctionToConstantFixer;
use PhpCsFixer\Fixer\LanguageConstruct\IsNullFixer;
use PhpCsFixer\Fixer\Operator\NewWithBracesFixer;
use PhpCsFixer\Fixer\Operator\StandardizeIncrementFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitMethodCasingFixer;
use PhpCsFixer\Fixer\StringNotation\ExplicitStringVariableFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
return ECSConfig::configure()->withRules([PhpUnitMethodCasingFixer::class, FunctionToConstantFixer::class, ExplicitStringVariableFixer::class, ExplicitIndirectVariableFixer::class, NewWithBracesFixer::class, StandardizeIncrementFixer::class, SelfAccessorFixer::class, MagicConstantCasingFixer::class, NoUselessElseFixer::class, SingleQuoteFixer::class, OrderedClassElementsFixer::class, IsNullFixer::class])->withConfiguredRule(SingleClassElementPerStatementFixer::class, ['elements' => ['const', 'property']])->withConfiguredRule(ClassDefinitionFixer::class, ['single_line' => \true])->withConfiguredRule(YodaStyleFixer::class, ['equal' => \false, 'identical' => \false, 'less_and_greater' => \false]);
