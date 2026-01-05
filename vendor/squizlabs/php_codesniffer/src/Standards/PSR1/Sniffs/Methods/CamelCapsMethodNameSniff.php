<?php

/**
 * Ensures method names are defined using camel case.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\PSR1\Sniffs\Methods;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Standards\Generic\Sniffs\NamingConventions\CamelCapsFunctionNameSniff as GenericCamelCapsFunctionNameSniff;
use PHP_CodeSniffer\Util\Common;
class CamelCapsMethodNameSniff extends GenericCamelCapsFunctionNameSniff
{
    /**
     * Processes the tokens within the scope.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being processed.
     * @param int                         $stackPtr  The position where this token was
     *                                               found.
     * @param int                         $currScope The position of the current scope.
     *
     * @return void
     */
    protected function processTokenWithinScope(File $phpcsFile, int $stackPtr, int $currScope)
    {
        $tokens = $phpcsFile->getTokens();
        // Determine if this is a function which needs to be examined.
        $conditions = $tokens[$stackPtr]['conditions'];
        \end($conditions);
        $deepestScope = \key($conditions);
        if ($deepestScope !== $currScope) {
            return;
        }
        $methodName = $phpcsFile->getDeclarationName($stackPtr);
        if ($methodName === '') {
            // Ignore live coding.
            return;
        }
        // Ignore magic methods.
        if (\preg_match('|^__[^_]|', $methodName) !== 0) {
            $magicPart = \strtolower((string) \substr($methodName, 2));
            if (isset(static::MAGIC_METHODS[$magicPart]) === \true || isset(static::DOUBLE_UNDERSCORE_METHODS[$magicPart]) === \true) {
                return;
            }
        }
        $testName = \ltrim($methodName, '_');
        if ($testName !== '' && Common::isCamelCaps($testName, \false, \true, \false) === \false) {
            $error = 'Method name "%s" is not in camel caps format';
            $className = '[Anonymous Class]';
            if ($tokens[$currScope]['code'] !== \T_ANON_CLASS) {
                $className = $phpcsFile->getDeclarationName($currScope);
            }
            $errorData = [$className . '::' . $methodName];
            $phpcsFile->addError($error, $stackPtr, 'NotCamelCaps', $errorData);
            $phpcsFile->recordMetric($stackPtr, 'CamelCase method name', 'no');
        } else {
            $phpcsFile->recordMetric($stackPtr, 'CamelCase method name', 'yes');
        }
    }
    /**
     * Processes the tokens outside the scope.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being processed.
     * @param int                         $stackPtr  The position where this token was
     *                                               found.
     *
     * @return void
     */
    protected function processTokenOutsideScope(File $phpcsFile, int $stackPtr)
    {
    }
}
