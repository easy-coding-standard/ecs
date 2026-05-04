<?php

/**
 * Verifies that class methods have scope modifiers.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\Squiz\Sniffs\Scope;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;
use PHP_CodeSniffer\Util\Tokens;
class MethodScopeSniff extends AbstractScopeSniff
{
    /**
     * Constructs a Squiz_Sniffs_Scope_MethodScopeSniff.
     */
    public function __construct()
    {
        parent::__construct(Tokens::OO_SCOPE_TOKENS, [\T_FUNCTION]);
    }
    /**
     * Processes the function tokens within the class.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file where this token was found.
     * @param int                         $stackPtr  The position where the token was found.
     * @param int                         $currScope The current scope opener token.
     *
     * @return void
     */
    protected function processTokenWithinScope(File $phpcsFile, int $stackPtr, int $currScope)
    {
        $tokens = $phpcsFile->getTokens();
        // Determine if this is a function which needs to be examined.
        $conditions = $tokens[$stackPtr]['conditions'];
        end($conditions);
        $deepestScope = key($conditions);
        if ($deepestScope !== $currScope) {
            return;
        }
        $methodName = $phpcsFile->getDeclarationName($stackPtr);
        if ($methodName === '') {
            // Ignore live coding.
            return;
        }
        $properties = $phpcsFile->getMethodProperties($stackPtr);
        if ($properties['scope_specified'] === \false) {
            $error = 'Visibility must be declared on method "%s"';
            $data = [$methodName];
            $phpcsFile->addError($error, $stackPtr, 'Missing', $data);
        }
    }
    /**
     * Processes a token that is found within the scope that this test is
     * listening to.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file where this token was found.
     * @param int                         $stackPtr  The position in the stack where this
     *                                               token was found.
     *
     * @return void
     */
    protected function processTokenOutsideScope(File $phpcsFile, int $stackPtr)
    {
    }
}
