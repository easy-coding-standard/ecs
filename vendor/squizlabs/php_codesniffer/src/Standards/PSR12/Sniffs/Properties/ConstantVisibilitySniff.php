<?php

/**
 * Verifies that all class constants have their visibility set.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\PSR12\Sniffs\Properties;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
class ConstantVisibilitySniff implements Sniff
{
    /**
     * Visibility tokens which are valid for class constants.
     *
     * @var array<int, int>
     */
    private const VALID_VISIBILITY = [\T_PRIVATE => \T_PRIVATE, \T_PUBLIC => \T_PUBLIC, \T_PROTECTED => \T_PROTECTED];
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int|string>
     */
    public function register()
    {
        return [\T_CONST];
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
        // Make sure this is a class constant.
        if ($phpcsFile->hasCondition($stackPtr, Tokens::OO_SCOPE_TOKENS) === \false) {
            return;
        }
        $ignore = Tokens::EMPTY_TOKENS;
        $ignore[] = \T_FINAL;
        $prev = $phpcsFile->findPrevious($ignore, $stackPtr - 1, null, \true);
        if (isset(self::VALID_VISIBILITY[$tokens[$prev]['code']]) === \true) {
            return;
        }
        $error = 'Visibility must be declared on all constants if your project supports PHP 7.1 or later';
        $phpcsFile->addWarning($error, $stackPtr, 'NotFound');
    }
}
