<?php

/**
 * Ensures that the value of a comparison is not assigned to a variable.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\Squiz\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
class DisallowComparisonAssignmentSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int|string>
     */
    public function register()
    {
        return [\T_EQUAL];
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
        // Ignore default value assignments in function definitions.
        $function = $phpcsFile->findPrevious(\T_FUNCTION, $stackPtr - 1, null, \false, null, \true);
        if ($function !== \false) {
            $opener = $tokens[$function]['parenthesis_opener'];
            $closer = $tokens[$function]['parenthesis_closer'];
            if ($opener < $stackPtr && $closer > $stackPtr) {
                return;
            }
        }
        // Ignore values in array definitions or match structures.
        $nextNonEmpty = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $stackPtr + 1, null, \true);
        if ($nextNonEmpty !== \false && ($tokens[$nextNonEmpty]['code'] === \T_ARRAY || $tokens[$nextNonEmpty]['code'] === \T_MATCH)) {
            return;
        }
        // Ignore function calls.
        $ignore = Tokens::NAME_TOKENS;
        $ignore[] = \T_NULLSAFE_OBJECT_OPERATOR;
        $ignore[] = \T_OBJECT_OPERATOR;
        $ignore[] = \T_VARIABLE;
        $ignore[] = \T_WHITESPACE;
        $next = $phpcsFile->findNext($ignore, $stackPtr + 1, null, \true);
        if ($tokens[$next]['code'] === \T_CLOSURE || $tokens[$next]['code'] === \T_OPEN_PARENTHESIS && isset(Tokens::NAME_TOKENS[$tokens[$next - 1]['code']]) === \true) {
            // Code will look like: $var = myFunction(
            // and will be ignored.
            return;
        }
        $endStatement = $phpcsFile->findEndOfStatement($stackPtr);
        for ($i = $stackPtr + 1; $i < $endStatement; $i++) {
            if (isset(Tokens::COMPARISON_TOKENS[$tokens[$i]['code']]) === \true && $tokens[$i]['code'] !== \T_COALESCE || $tokens[$i]['code'] === \T_INLINE_THEN) {
                $error = 'The value of a comparison must not be assigned to a variable';
                $phpcsFile->addError($error, $stackPtr, 'AssignedComparison');
                break;
            }
            if (isset(Tokens::BOOLEAN_OPERATORS[$tokens[$i]['code']]) === \true || $tokens[$i]['code'] === \T_BOOLEAN_NOT) {
                $error = 'The value of a boolean operation must not be assigned to a variable';
                $phpcsFile->addError($error, $stackPtr, 'AssignedBool');
                break;
            }
        }
    }
}
