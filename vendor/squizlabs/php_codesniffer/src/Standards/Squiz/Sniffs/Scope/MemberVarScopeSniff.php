<?php

/**
 * Verifies that class members have scope modifiers.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\Squiz\Sniffs\Scope;

use PHP_CodeSniffer\Exceptions\RuntimeException;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;
use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;
use PHP_CodeSniffer\Util\Tokens;
class MemberVarScopeSniff extends AbstractVariableSniff
{
    /**
     * Only listen to variables within OO scopes.
     */
    public function __construct()
    {
        AbstractScopeSniff::__construct(Tokens::OO_SCOPE_TOKENS, [\T_VARIABLE], \false);
    }
    /**
     * Processes the function tokens within the class.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file where this token was found.
     * @param int                         $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processMemberVar(File $phpcsFile, int $stackPtr)
    {
        try {
            $properties = $phpcsFile->getMemberProperties($stackPtr);
        } catch (RuntimeException $e) {
            // Parse error: property in enum. Ignore.
            return;
        }
        if ($properties['scope_specified'] !== \false) {
            return;
        }
        $tokens = $phpcsFile->getTokens();
        if ($properties['set_scope'] === \false) {
            $error = 'Scope modifier not specified for member variable "%s"';
            $data = [$tokens[$stackPtr]['content']];
            $phpcsFile->addError($error, $stackPtr, 'Missing', $data);
        } else {
            $error = 'Read scope modifier not specified for member variable "%s"';
            $data = [$tokens[$stackPtr]['content']];
            $phpcsFile->addError($error, $stackPtr, 'AsymReadMissing', $data);
        }
    }
    /**
     * Processes normal variables.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file where this token was found.
     * @param int                         $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariable(File $phpcsFile, int $stackPtr)
    {
        // We don't care about normal variables.
    }
    /**
     * Processes variables in double quoted strings.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file where this token was found.
     * @param int                         $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariableInString(File $phpcsFile, int $stackPtr)
    {
        // We don't care about normal variables.
    }
}
