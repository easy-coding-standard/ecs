<?php

/**
 * Bans the use of size-based functions in loop conditions.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\Squiz\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
class DisallowSizeFunctionsInLoopsSniff implements Sniff
{
    /**
     * An array of functions we don't want in the condition of loops.
     *
     * @var array<string, true>
     */
    protected const FORBIDDEN_FUNCTIONS = ['sizeof' => \true, 'strlen' => \true, 'count' => \true];
    /**
     * An array of functions we don't want in the condition of loops.
     *
     * @var array<string, true>
     *
     * @deprecated 4.0.0 Use the DisallowSizeFunctionsInLoopsSniff::FORBIDDEN_FUNCTIONS constant instead.
     */
    protected $forbiddenFunctions = self::FORBIDDEN_FUNCTIONS;
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int|string>
     */
    public function register()
    {
        return [\T_WHILE, \T_FOR];
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
        $openBracket = $tokens[$stackPtr]['parenthesis_opener'];
        $closeBracket = $tokens[$stackPtr]['parenthesis_closer'];
        if ($tokens[$stackPtr]['code'] === \T_FOR) {
            // We only want to check the condition in FOR loops.
            $start = $phpcsFile->findNext(\T_SEMICOLON, $openBracket + 1);
            $end = $phpcsFile->findPrevious(\T_SEMICOLON, $closeBracket - 1);
        } else {
            $start = $openBracket;
            $end = $closeBracket;
        }
        for ($i = $start + 1; $i < $end; $i++) {
            if (($tokens[$i]['code'] === \T_STRING || $tokens[$i]['code'] === \T_NAME_FULLY_QUALIFIED) && isset(static::FORBIDDEN_FUNCTIONS[ltrim($tokens[$i]['content'], '\\')]) === \true) {
                $functionName = $tokens[$i]['content'];
                // Make sure it isn't a member var.
                if ($tokens[$i - 1]['code'] === \T_OBJECT_OPERATOR || $tokens[$i - 1]['code'] === \T_NULLSAFE_OBJECT_OPERATOR) {
                    continue;
                }
                $functionName .= '()';
                $error = 'The use of %s inside a loop condition is not allowed; assign the return value to a variable and use the variable in the loop condition instead';
                $data = [$functionName];
                $phpcsFile->addError($error, $i, 'Found', $data);
            }
        }
    }
}
