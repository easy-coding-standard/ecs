<?php

/**
 * Discourages the use of deprecated PHP functions.
 *
 * @author    Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\Generic\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use ReflectionFunction;
class DeprecatedFunctionsSniff extends \PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\ForbiddenFunctionsSniff
{
    /**
     * A list of forbidden functions with their alternatives.
     *
     * The value is NULL if no alternative exists. IE, the
     * function should just not be used.
     *
     * @var array<string, string|null>
     */
    public $forbiddenFunctions = [];
    /**
     * Constructor.
     *
     * Uses the Reflection API to get a list of deprecated functions.
     */
    public function __construct()
    {
        $functions = \get_defined_functions();
        foreach ($functions['internal'] as $functionName) {
            $function = new ReflectionFunction($functionName);
            if ($function->isDeprecated() === \true) {
                $this->forbiddenFunctions[$functionName] = null;
            }
        }
    }
    /**
     * Generates the error or warning for this sniff.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile    The file being scanned.
     * @param int                         $stackPtr     The position of the forbidden function
     *                                                  in the token array.
     * @param string                      $functionName The name of the forbidden function.
     * @param string|null                 $pattern      The pattern used for the match.
     *
     * @return void
     */
    protected function addError(File $phpcsFile, int $stackPtr, string $functionName, ?string $pattern = null)
    {
        $data = [$functionName];
        $error = 'Function %s() has been deprecated';
        $type = 'Deprecated';
        if ($this->error === \true) {
            $phpcsFile->addError($error, $stackPtr, $type, $data);
        } else {
            $phpcsFile->addWarning($error, $stackPtr, $type, $data);
        }
    }
}
