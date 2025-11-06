<?php

/**
 * A Sniff to enforce the use of IDENTICAL type operators rather than EQUAL operators.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\Squiz\Sniffs\Operators;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
class ComparisonOperatorUsageSniff implements Sniff
{
    /**
     * A list of valid comparison operators.
     *
     * @var array<int|string, true>
     */
    private const VALID_OPERATIONS = [\T_IS_IDENTICAL => \true, \T_IS_NOT_IDENTICAL => \true, \T_LESS_THAN => \true, \T_GREATER_THAN => \true, \T_IS_GREATER_OR_EQUAL => \true, \T_IS_SMALLER_OR_EQUAL => \true, \T_INSTANCEOF => \true];
    /**
     * A list of invalid operators with their alternatives.
     *
     * @var array<int|string, string>
     */
    private const INVALID_OPERATIONS = [\T_IS_EQUAL => '===', \T_IS_NOT_EQUAL => '!==', \T_BOOLEAN_NOT => '=== FALSE'];
    /**
     * Registers the token types that this sniff wishes to listen to.
     *
     * @return array<int|string>
     */
    public function register()
    {
        return [\T_IF, \T_ELSEIF, \T_INLINE_THEN, \T_WHILE, \T_FOR];
    }
    /**
     * Process the tokens that this sniff is listening for.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file where the token was found.
     * @param int                         $stackPtr  The position in the stack where the token
     *                                               was found.
     *
     * @return void
     */
    public function process(File $phpcsFile, int $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if ($tokens[$stackPtr]['code'] === \T_INLINE_THEN) {
            $end = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $stackPtr - 1, null, \true);
            if ($tokens[$end]['code'] !== \T_CLOSE_PARENTHESIS) {
                // This inline IF statement does not have its condition
                // bracketed, so we need to guess where it starts.
                for ($i = $end - 1; $i >= 0; $i--) {
                    if ($tokens[$i]['code'] === \T_SEMICOLON) {
                        // Stop here as we assume it is the end
                        // of the previous statement.
                        break;
                    } elseif ($tokens[$i]['code'] === \T_OPEN_TAG) {
                        // Stop here as this is the start of the file.
                        break;
                    } elseif ($tokens[$i]['code'] === \T_CLOSE_CURLY_BRACKET) {
                        // Stop if this is the closing brace of
                        // a code block.
                        if (isset($tokens[$i]['scope_opener']) === \true) {
                            break;
                        }
                    } elseif ($tokens[$i]['code'] === \T_OPEN_CURLY_BRACKET) {
                        // Stop if this is the opening brace of
                        // a code block.
                        if (isset($tokens[$i]['scope_closer']) === \true) {
                            break;
                        }
                    } elseif ($tokens[$i]['code'] === \T_OPEN_PARENTHESIS) {
                        // Stop if this is the start of a pair of
                        // parentheses that surrounds the inline
                        // IF statement.
                        if (isset($tokens[$i]['parenthesis_closer']) === \true && $tokens[$i]['parenthesis_closer'] >= $stackPtr) {
                            break;
                        }
                    }
                }
                $start = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $i + 1, null, \true);
            } else {
                if (isset($tokens[$end]['parenthesis_opener']) === \false) {
                    return;
                }
                $start = $tokens[$end]['parenthesis_opener'];
            }
        } elseif ($tokens[$stackPtr]['code'] === \T_FOR) {
            if (isset($tokens[$stackPtr]['parenthesis_opener']) === \false) {
                return;
            }
            $openingBracket = $tokens[$stackPtr]['parenthesis_opener'];
            $closingBracket = $tokens[$stackPtr]['parenthesis_closer'];
            $start = $phpcsFile->findNext(\T_SEMICOLON, $openingBracket, $closingBracket);
            $end = $phpcsFile->findNext(\T_SEMICOLON, $start + 1, $closingBracket);
            if ($start === \false || $end === \false) {
                return;
            }
        } else {
            if (isset($tokens[$stackPtr]['parenthesis_opener']) === \false) {
                return;
            }
            $start = $tokens[$stackPtr]['parenthesis_opener'];
            $end = $tokens[$stackPtr]['parenthesis_closer'];
        }
        $requiredOps = 0;
        $foundOps = 0;
        $foundBooleans = 0;
        $lastNonEmpty = $start;
        for ($i = $start; $i <= $end; $i++) {
            $type = $tokens[$i]['code'];
            if (isset(self::INVALID_OPERATIONS[$type]) === \true) {
                $error = 'Operator %s prohibited; use %s instead';
                $data = [$tokens[$i]['content'], self::INVALID_OPERATIONS[$type]];
                $phpcsFile->addError($error, $i, 'NotAllowed', $data);
                $foundOps++;
            } elseif (isset(self::VALID_OPERATIONS[$type]) === \true) {
                $foundOps++;
            }
            if ($type === \T_OPEN_PARENTHESIS && isset($tokens[$i]['parenthesis_closer']) === \true && isset(Tokens::FUNCTION_NAME_TOKENS[$tokens[$lastNonEmpty]['code']]) === \true) {
                $i = $tokens[$i]['parenthesis_closer'];
                $lastNonEmpty = $i;
                continue;
            }
            if ($tokens[$i]['code'] === \T_TRUE || $tokens[$i]['code'] === \T_FALSE) {
                $foundBooleans++;
            }
            if ($tokens[$i]['code'] === \T_BOOLEAN_AND || $tokens[$i]['code'] === \T_BOOLEAN_OR) {
                $requiredOps++;
                // When the instanceof operator is used with another operator
                // like ===, you can get more ops than are required.
                if ($foundOps > $requiredOps) {
                    $foundOps = $requiredOps;
                }
                // If we get to here and we have not found the right number of
                // comparison operators, then we must have had an implicit
                // true operation i.e., if ($a) instead of the required
                // if ($a === true), so let's add an error.
                if ($requiredOps !== $foundOps) {
                    $error = 'Implicit true comparisons prohibited; use === TRUE instead';
                    $phpcsFile->addError($error, $stackPtr, 'ImplicitTrue');
                    $foundOps++;
                }
            }
            if (isset(Tokens::EMPTY_TOKENS[$type]) === \false) {
                $lastNonEmpty = $i;
            }
        }
        $requiredOps++;
        if ($foundOps < $requiredOps && $requiredOps !== $foundBooleans) {
            $error = 'Implicit true comparisons prohibited; use === TRUE instead';
            $phpcsFile->addError($error, $stackPtr, 'ImplicitTrue');
        }
    }
}
