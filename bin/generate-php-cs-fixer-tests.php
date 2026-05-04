<?php

declare (strict_types=1);
namespace ECSPrefix202605;

use PhpCsFixer\RuleSet\RuleSets;
use ECSPrefix202605\PhpParser\Modifiers;
use ECSPrefix202605\PhpParser\Node\Expr\ArrayDimFetch;
use ECSPrefix202605\PhpParser\Node\Expr\Assign;
use ECSPrefix202605\PhpParser\Node\Expr\ConstFetch;
use ECSPrefix202605\PhpParser\Node\Expr\PropertyFetch;
use ECSPrefix202605\PhpParser\Node\Expr\Variable;
use ECSPrefix202605\PhpParser\Node\Identifier;
use ECSPrefix202605\PhpParser\Node\Name;
use ECSPrefix202605\PhpParser\Node\Param;
use ECSPrefix202605\PhpParser\Node\Scalar\String_;
use ECSPrefix202605\PhpParser\Node\Stmt\ClassMethod;
use ECSPrefix202605\PhpParser\Node\Stmt\Expression;
use ECSPrefix202605\PhpParser\Node\Stmt\If_;
use ECSPrefix202605\PhpParser\Node\Stmt\Return_;
use ECSPrefix202605\PhpParser\PrettyPrinter\Standard;
// this helper script generates the withPhpCsFixerSets() method for ECSConfigBuilder class
require __DIR__ . '/../vendor/autoload.php';
$setsDirectory = __DIR__ . '/../vendor/friendsofphp/php-cs-fixer/src/RuleSet/Sets/';
$setDefinitions = RuleSets::getSetDefinitions();
$setNames = [];
foreach ($setDefinitions as $setDefinition) {
    $setNames[] = $setDefinition->getName();
}
// create withPhpCsFixerSets() method here
$classMethod = new ClassMethod('withPhpCsFixerSets');
$classMethod->flags = Modifiers::PUBLIC;
$classMethod->returnType = new Name('self');
foreach ($setNames as $setName) {
    // convert to PHP variable name
    $paramName = \ltrim($setName, '@');
    $paramName = lowercaseUntilFirstLower($paramName);
    $paramName = \str_replace(':r', 'R', $paramName);
    $paramName = \str_replace(['.', '-', '_'], '', $paramName);
    // lowercase only the first uppercase letters
    $classMethod->params[] = new Param(new Variable($paramName), new ConstFetch(new Name('false')), new Identifier('bool'));
    $dynamicSetsPropertyFetch = new PropertyFetch(new Variable('this'), 'dynamicSets');
    $classMethod->stmts[] = new If_(new Variable($paramName), ['stmts' => [new Expression(new Assign(new ArrayDimFetch($dynamicSetsPropertyFetch), new String_($setName)))]]);
}
function lowercaseUntilFirstLower($input): string
{
    $output = '';
    $foundLower = \false;
    for ($i = 0; $i < \strlen((string) $input); $i++) {
        $char = $input[$i];
        if (!$foundLower && \ctype_upper((string) $char)) {
            $output .= \strtolower((string) $char);
        } else {
            $output .= $char;
            $foundLower = \true;
        }
    }
    return $output;
}
// add dynamic set includes
$classMethod->stmts[] = new Return_(new Variable('this'));
$printerStandard = new Standard();
echo $printerStandard->prettyPrint([$classMethod]);
