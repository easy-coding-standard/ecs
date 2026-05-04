<?php

/**
 * Checks against empty PHP statements.
 *
 * - Check against two semicolons with no executable code in between.
 * - Check against an empty PHP open - close tag combination.
 *
 * @author    Juliette Reinders Folmer <phpcs_nospam@adviesenzo.nl>
 * @copyright 2017 Juliette Reinders Folmer. All rights reserved.
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
class EmptyPHPStatementSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int|string>
     */
    public function register()
    {
        return [\T_SEMICOLON, \T_CLOSE_TAG];
    }
    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, int $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if ($tokens[$stackPtr]['code'] === \T_SEMICOLON) {
            $this->processSemicolon($phpcsFile, $stackPtr);
        } else {
            $this->processCloseTag($phpcsFile, $stackPtr);
        }
    }
    /**
     * Detect `something();;`.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    private function processSemicolon(File $phpcsFile, int $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $prevNonEmpty = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $stackPtr - 1, null, \true);
        if ($tokens[$prevNonEmpty]['code'] !== \T_SEMICOLON && $tokens[$prevNonEmpty]['code'] !== \T_OPEN_TAG && $tokens[$prevNonEmpty]['code'] !== \T_OPEN_TAG_WITH_ECHO) {
            if (isset($tokens[$prevNonEmpty]['scope_condition']) === \false) {
                return;
            }
            if ($tokens[$prevNonEmpty]['scope_opener'] !== $prevNonEmpty && $tokens[$prevNonEmpty]['code'] !== \T_CLOSE_CURLY_BRACKET) {
                return;
            }
            $scopeOwner = $tokens[$tokens[$prevNonEmpty]['scope_condition']]['code'];
            if ($scopeOwner === \T_CLOSURE || $scopeOwner === \T_ANON_CLASS || $scopeOwner === \T_MATCH) {
                return;
            }
            // Else, it's something like `if (foo) {};` and the semicolon is not needed.
        }
        if (isset($tokens[$stackPtr]['nested_parenthesis']) === \true) {
            $nested = $tokens[$stackPtr]['nested_parenthesis'];
            $lastCloser = array_pop($nested);
            if (isset($tokens[$lastCloser]['parenthesis_owner']) === \true && $tokens[$tokens[$lastCloser]['parenthesis_owner']]['code'] === \T_FOR) {
                // Empty for() condition.
                return;
            }
        }
        $fix = $phpcsFile->addFixableWarning('Empty PHP statement detected: superfluous semicolon.', $stackPtr, 'SemicolonWithoutCodeDetected');
        if ($fix === \true) {
            $phpcsFile->fixer->beginChangeset();
            // Make sure there always remains one space between the open tag and the next content.
            $replacement = ' ';
            if ($tokens[$stackPtr + 1]['code'] === \T_WHITESPACE) {
                $replacement = '';
            }
            $phpcsFile->fixer->replaceToken($stackPtr, $replacement);
            for ($i = $stackPtr - 1; $i > $prevNonEmpty; $i--) {
                if ($tokens[$i]['code'] !== \T_SEMICOLON && $tokens[$i]['code'] !== \T_WHITESPACE) {
                    break;
                }
                $phpcsFile->fixer->replaceToken($i, '');
            }
            $phpcsFile->fixer->endChangeset();
        }
    }
    /**
     * Detect `<?php ? >`.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    private function processCloseTag(File $phpcsFile, int $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $prevNonEmpty = $phpcsFile->findPrevious(\T_WHITESPACE, $stackPtr - 1, null, \true);
        if ($tokens[$prevNonEmpty]['code'] !== \T_OPEN_TAG && $tokens[$prevNonEmpty]['code'] !== \T_OPEN_TAG_WITH_ECHO) {
            return;
        }
        $fix = $phpcsFile->addFixableWarning('Empty PHP open/close tag combination detected.', $prevNonEmpty, 'EmptyPHPOpenCloseTagsDetected');
        if ($fix === \true) {
            $phpcsFile->fixer->beginChangeset();
            for ($i = $prevNonEmpty; $i <= $stackPtr; $i++) {
                $phpcsFile->fixer->replaceToken($i, '');
            }
            $phpcsFile->fixer->endChangeset();
        }
    }
}
