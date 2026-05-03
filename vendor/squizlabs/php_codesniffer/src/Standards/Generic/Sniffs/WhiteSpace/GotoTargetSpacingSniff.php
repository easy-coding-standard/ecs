<?php

/**
 * Ensures there is no space between the label for a goto target and the colon following it.
 *
 * @copyright 2025 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\Generic\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
class GotoTargetSpacingSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int|string>
     */
    public function register()
    {
        return [\T_GOTO_LABEL];
    }
    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in
     *                                               the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, int $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if ($tokens[$stackPtr + 1]['code'] === \T_GOTO_COLON) {
            return;
        }
        $nextNonWhiteSpace = $phpcsFile->findNext(\T_WHITESPACE, $stackPtr + 1, null, \true);
        $nextNonEmpty = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $stackPtr + 1, null, \true);
        if ($nextNonWhiteSpace !== $nextNonEmpty) {
            $found = 'comment';
        } elseif ($tokens[$stackPtr]['line'] !== $tokens[$nextNonWhiteSpace]['line']) {
            $found = 'newline';
        } elseif ($tokens[$stackPtr + 1]['length'] === 1) {
            $found = '1 space';
        } else {
            $found = $tokens[$stackPtr + 1]['length'] . ' spaces';
        }
        $error = 'There should be no space between goto label "%s" and the colon following it. Found: %s';
        $data = [$tokens[$stackPtr]['content'], $found];
        if ($nextNonWhiteSpace !== $nextNonEmpty) {
            $phpcsFile->addError($error, $stackPtr, 'CommentFound', $data);
            return;
        }
        $fix = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceFound', $data);
        if ($fix === \true) {
            $phpcsFile->fixer->beginChangeset();
            for ($i = $stackPtr + 1; $tokens[$i]['code'] === \T_WHITESPACE; $i++) {
                $phpcsFile->fixer->replaceToken($i, '');
            }
            $phpcsFile->fixer->endChangeset();
        }
    }
}
