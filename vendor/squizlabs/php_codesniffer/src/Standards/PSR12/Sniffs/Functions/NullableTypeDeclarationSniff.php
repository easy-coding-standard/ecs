<?php

/**
 * Verifies that nullable typehints are lacking superfluous whitespace, e.g. ?int
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\PSR12\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
class NullableTypeDeclarationSniff implements Sniff
{
    /**
     * An array of valid tokens after `T_NULLABLE` occurrences.
     *
     * @var array<int|string, int|string>
     */
    private const VALID_TOKENS = Tokens::NAME_TOKENS + [\T_CALLABLE => \T_CALLABLE, \T_SELF => \T_SELF, \T_PARENT => \T_PARENT, \T_STATIC => \T_STATIC, \T_NULL => \T_NULL, \T_FALSE => \T_FALSE, \T_TRUE => \T_TRUE];
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int|string>
     */
    public function register()
    {
        return [\T_NULLABLE];
    }
    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the
     *                                               stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, int $stackPtr)
    {
        $nextNonEmptyPtr = $phpcsFile->findNext([\T_WHITESPACE], $stackPtr + 1, null, \true);
        if ($nextNonEmptyPtr === \false) {
            // Parse error or live coding.
            return;
        }
        $tokens = $phpcsFile->getTokens();
        $nextNonEmptyCode = $tokens[$nextNonEmptyPtr]['code'];
        $validTokenFound = isset(self::VALID_TOKENS[$nextNonEmptyCode]);
        if ($validTokenFound === \true && $nextNonEmptyPtr === $stackPtr + 1) {
            // Valid structure.
            return;
        }
        $error = 'There must not be a space between the question mark and the type in nullable type declarations';
        if ($validTokenFound === \true) {
            // No other tokens then whitespace tokens found; fixable.
            $fix = $phpcsFile->addFixableError($error, $stackPtr, 'WhitespaceFound');
            if ($fix === \true) {
                for ($i = $stackPtr + 1; $i < $nextNonEmptyPtr; $i++) {
                    $phpcsFile->fixer->replaceToken($i, '');
                }
            }
            return;
        }
        // Non-whitespace tokens found; trigger error but don't fix.
        $phpcsFile->addError($error, $stackPtr, 'UnexpectedCharactersFound');
    }
}
