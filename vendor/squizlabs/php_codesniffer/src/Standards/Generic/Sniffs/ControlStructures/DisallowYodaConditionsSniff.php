<?php

/**
 * Ban the use of Yoda conditions.
 *
 * @author    Mponos George <gmponos@gmail.com>
 * @author    Mark Scherer <username@example.com>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\Generic\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
class DisallowYodaConditionsSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int|string>
     */
    public function register()
    {
        $tokens = Tokens::COMPARISON_TOKENS;
        unset($tokens[\T_COALESCE]);
        return $tokens;
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
        $previousIndex = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $stackPtr - 1, null, \true);
        $relevantTokens = [\T_CLOSE_SHORT_ARRAY, \T_CLOSE_PARENTHESIS, \T_TRUE, \T_FALSE, \T_NULL, \T_LNUMBER, \T_DNUMBER, \T_CONSTANT_ENCAPSED_STRING];
        if (\in_array($tokens[$previousIndex]['code'], $relevantTokens, \true) === \false) {
            return;
        }
        if ($tokens[$previousIndex]['code'] === \T_CLOSE_SHORT_ARRAY) {
            $previousIndex = $tokens[$previousIndex]['bracket_opener'];
            if ($this->isArrayStatic($phpcsFile, $previousIndex) === \false) {
                return;
            }
        }
        $prevIndex = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $previousIndex - 1, null, \true);
        if (\in_array($tokens[$prevIndex]['code'], Tokens::ARITHMETIC_TOKENS, \true) === \true) {
            return;
        }
        if ($tokens[$prevIndex]['code'] === \T_STRING_CONCAT) {
            return;
        }
        // Is it a parenthesis.
        if ($tokens[$previousIndex]['code'] === \T_CLOSE_PARENTHESIS) {
            $beforeOpeningParenthesisIndex = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $tokens[$previousIndex]['parenthesis_opener'] - 1, null, \true);
            if ($beforeOpeningParenthesisIndex === \false || $tokens[$beforeOpeningParenthesisIndex]['code'] !== \T_ARRAY) {
                if (isset(Tokens::NAME_TOKENS[$tokens[$beforeOpeningParenthesisIndex]['code']]) === \true) {
                    return;
                }
                // If it is not an array, check what is inside.
                $found = $phpcsFile->findPrevious(\T_VARIABLE, $previousIndex - 1, $tokens[$previousIndex]['parenthesis_opener']);
                // If a variable exists, it is not Yoda.
                if ($found !== \false) {
                    return;
                }
                // If there is nothing inside the parenthesis, it is not a Yoda condition.
                $opener = $tokens[$previousIndex]['parenthesis_opener'];
                $prev = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $previousIndex - 1, $opener + 1, \true);
                if ($prev === \false) {
                    return;
                }
            } elseif ($this->isArrayStatic($phpcsFile, $beforeOpeningParenthesisIndex) === \false) {
                return;
            }
        }
        $phpcsFile->addError('Usage of Yoda conditions is not allowed; switch the expression order', $stackPtr, 'Found');
    }
    /**
     * Determines if an array is a static definition.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile  The file being scanned.
     * @param int                         $arrayToken The position of the array token.
     *
     * @return bool
     */
    public function isArrayStatic(File $phpcsFile, int $arrayToken)
    {
        $tokens = $phpcsFile->getTokens();
        if ($tokens[$arrayToken]['code'] === \T_OPEN_SHORT_ARRAY) {
            $start = $arrayToken;
            $end = $tokens[$arrayToken]['bracket_closer'];
        } elseif ($tokens[$arrayToken]['code'] === \T_ARRAY) {
            $start = $tokens[$arrayToken]['parenthesis_opener'];
            $end = $tokens[$arrayToken]['parenthesis_closer'];
        } else {
            // Shouldn't be possible but may happen if external sniffs are using this method.
            return \true;
            // @codeCoverageIgnore
        }
        $staticTokens = Tokens::EMPTY_TOKENS;
        $staticTokens += Tokens::TEXT_STRING_TOKENS;
        $staticTokens += Tokens::ASSIGNMENT_TOKENS;
        $staticTokens += Tokens::EQUALITY_TOKENS;
        $staticTokens += Tokens::COMPARISON_TOKENS;
        $staticTokens += Tokens::ARITHMETIC_TOKENS;
        $staticTokens += Tokens::OPERATORS;
        $staticTokens += Tokens::BOOLEAN_OPERATORS;
        $staticTokens += Tokens::CAST_TOKENS;
        $staticTokens += Tokens::BRACKET_TOKENS;
        $staticTokens += [\T_DOUBLE_ARROW => \T_DOUBLE_ARROW, \T_COMMA => \T_COMMA, \T_TRUE => \T_TRUE, \T_FALSE => \T_FALSE, \T_NULL => \T_NULL];
        for ($i = $start + 1; $i < $end; $i++) {
            if (isset($tokens[$i]['scope_closer']) === \true) {
                $i = $tokens[$i]['scope_closer'];
                continue;
            }
            if (isset($staticTokens[$tokens[$i]['code']]) === \false) {
                return \false;
            }
        }
        return \true;
    }
}
