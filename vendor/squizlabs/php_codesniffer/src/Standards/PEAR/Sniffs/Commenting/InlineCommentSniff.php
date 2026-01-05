<?php

/**
 * Checks that no Perl-style comments are used.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
class InlineCommentSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int|string>
     */
    public function register()
    {
        return [\T_COMMENT];
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
        if ($tokens[$stackPtr]['content'][0] === '#') {
            $phpcsFile->recordMetric($stackPtr, 'Inline comment style', '# ...');
            $error = 'Perl-style comments are not allowed. Use "// Comment."';
            $error .= ' or "/* comment */" instead.';
            $fix = $phpcsFile->addFixableError($error, $stackPtr, 'WrongStyle');
            if ($fix === \true) {
                $newComment = \ltrim($tokens[$stackPtr]['content'], '# ');
                $newComment = '// ' . $newComment;
                $phpcsFile->fixer->replaceToken($stackPtr, $newComment);
            }
        } elseif ($tokens[$stackPtr]['content'][0] === '/' && $tokens[$stackPtr]['content'][1] === '/') {
            $phpcsFile->recordMetric($stackPtr, 'Inline comment style', '// ...');
        } elseif ($tokens[$stackPtr]['content'][0] === '/' && $tokens[$stackPtr]['content'][1] === '*') {
            $phpcsFile->recordMetric($stackPtr, 'Inline comment style', '/* ... */');
        }
    }
}
