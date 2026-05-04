<?php

/**
 * Ensure cast statements don't contain whitespace.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
class CastSpacingSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int|string>
     */
    public function register()
    {
        return Tokens::CAST_TOKENS;
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
        $tokens = $phpcsFile->getTokens();
        $content = $tokens[$stackPtr]['content'];
        $expected = str_replace(' ', '', $content);
        $expected = str_replace("\t", '', $expected);
        if ($content !== $expected) {
            $error = 'Cast statements must not contain whitespace; expected "%s" but found "%s"';
            $data = [$expected, $content];
            $fix = $phpcsFile->addFixableError($error, $stackPtr, 'ContainsWhiteSpace', $data);
            if ($fix === \true) {
                $phpcsFile->fixer->replaceToken($stackPtr, $expected);
            }
        }
    }
}
