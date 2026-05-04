<?php

/**
 * Tests that the file name and the name of the class contained within the file match.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\Squiz\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
class ClassFileNameSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int|string>
     */
    public function register()
    {
        $targets = Tokens::OO_SCOPE_TOKENS;
        unset($targets[\T_ANON_CLASS]);
        return $targets;
    }
    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in
     *                                               the stack passed in $tokens.
     *
     * @return int|void Integer stack pointer to skip forward or void to continue
     *                  normal file processing.
     */
    public function process(File $phpcsFile, int $stackPtr)
    {
        $fullPath = $phpcsFile->getFilename();
        if ($fullPath === 'STDIN') {
            return $phpcsFile->numTokens;
        }
        $fileName = basename($fullPath);
        $fileNoExt = (string) substr($fileName, 0, strrpos($fileName, '.'));
        $extension = (string) substr($fileName, strrpos($fileName, '.') + 1);
        $tokens = $phpcsFile->getTokens();
        $ooName = $phpcsFile->getDeclarationName($stackPtr);
        if ($ooName === '') {
            // Probably parse error/live coding.
            return;
        }
        if ($ooName !== $fileNoExt) {
            $error = 'Filename doesn\'t match %s name; expected file name "%s"';
            $data = [$tokens[$stackPtr]['content'], $ooName . '.' . $extension];
            $phpcsFile->addError($error, $stackPtr, 'NoMatch', $data);
        }
    }
}
