<?php

/**
 * Checks that only one trait is declared per file.
 *
 * @author    Alexander Obuhovich <aik.bold@gmail.com>
 * @copyright 2010-2014 Alexander Obuhovich
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\Generic\Sniffs\Files;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
class OneTraitPerFileSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int|string>
     */
    public function register()
    {
        return [\T_TRAIT];
    }
    /**
     * Processes this sniff, when one of its tokens is encountered.
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
        $start = $stackPtr + 1;
        if (isset($tokens[$stackPtr]['scope_closer']) === \true) {
            $start = $tokens[$stackPtr]['scope_closer'] + 1;
        }
        $nextClass = $phpcsFile->findNext($this->register(), $start);
        if ($nextClass !== \false) {
            $error = 'Only one trait is allowed in a file';
            $phpcsFile->addError($error, $nextClass, 'MultipleFound');
        }
    }
}
