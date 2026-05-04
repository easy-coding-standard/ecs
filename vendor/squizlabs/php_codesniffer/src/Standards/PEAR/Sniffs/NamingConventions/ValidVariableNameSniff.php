<?php

/**
 * Checks the naming of member variables.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\PEAR\Sniffs\NamingConventions;

use PHP_CodeSniffer\Exceptions\RuntimeException;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;
use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;
use PHP_CodeSniffer\Util\Tokens;
class ValidVariableNameSniff extends AbstractVariableSniff
{
    /**
     * Only listen to variables within OO scopes.
     */
    public function __construct()
    {
        AbstractScopeSniff::__construct(Tokens::OO_SCOPE_TOKENS, [\T_VARIABLE], \false);
    }
    /**
     * Processes class member variables.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    protected function processMemberVar(File $phpcsFile, int $stackPtr)
    {
        try {
            $memberProps = $phpcsFile->getMemberProperties($stackPtr);
        } catch (RuntimeException $e) {
            // Parse error: property in enum. Ignore.
            return;
        }
        $tokens = $phpcsFile->getTokens();
        $memberName = ltrim($tokens[$stackPtr]['content'], '$');
        $scope = $memberProps['scope'];
        $scopeSpecified = $memberProps['scope_specified'];
        if ($scopeSpecified === \false && $memberProps['set_scope'] !== \false) {
            // Implicit `public` visibility for property with asymmetric visibility.
            $scopeSpecified = \true;
        }
        if ($memberProps['scope'] === 'private') {
            $isPublic = \false;
        } else {
            $isPublic = \true;
        }
        // If it's a private member, it must have an underscore on the front.
        if ($isPublic === \false && $memberName[0] !== '_') {
            $error = 'Private member variable "%s" must be prefixed with an underscore';
            $data = [$memberName];
            $phpcsFile->addError($error, $stackPtr, 'PrivateNoUnderscore', $data);
            return;
        }
        // If it's not a private member, it must not have an underscore on the front.
        if ($isPublic === \true && $scopeSpecified === \true && $memberName[0] === '_') {
            $error = '%s member variable "%s" must not be prefixed with an underscore';
            $data = [ucfirst($scope), $memberName];
            $phpcsFile->addError($error, $stackPtr, 'PublicUnderscore', $data);
            return;
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
