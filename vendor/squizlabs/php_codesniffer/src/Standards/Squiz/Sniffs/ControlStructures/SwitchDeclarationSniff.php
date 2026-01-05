<?php

/**
 * Enforces switch statement formatting.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\Squiz\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
class SwitchDeclarationSniff implements Sniff
{
    /**
     * The number of spaces code should be indented.
     *
     * @var integer
     */
    public $indent = 4;
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int|string>
     */
    public function register()
    {
        return [\T_SWITCH];
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
        // We can't process SWITCH statements unless we know where they start and end.
        if (isset($tokens[$stackPtr]['scope_opener']) === \false || isset($tokens[$stackPtr]['scope_closer']) === \false) {
            return;
        }
        $switch = $tokens[$stackPtr];
        $nextCase = $stackPtr;
        $caseAlignment = $switch['column'] + $this->indent;
        $caseCount = 0;
        $foundDefault = \false;
        while (($nextCase = $phpcsFile->findNext([\T_CASE, \T_DEFAULT, \T_SWITCH], $nextCase + 1, $switch['scope_closer'])) !== \false) {
            // Skip nested SWITCH statements; they are handled on their own.
            if ($tokens[$nextCase]['code'] === \T_SWITCH) {
                $nextCase = $tokens[$nextCase]['scope_closer'];
                continue;
            }
            if ($tokens[$nextCase]['code'] === \T_DEFAULT) {
                $type = 'Default';
                $foundDefault = \true;
            } else {
                $type = 'Case';
                $caseCount++;
            }
            if ($tokens[$nextCase]['content'] !== \strtolower($tokens[$nextCase]['content'])) {
                $expected = \strtolower($tokens[$nextCase]['content']);
                $error = \strtoupper($type) . ' keyword must be lowercase; expected "%s" but found "%s"';
                $data = [$expected, $tokens[$nextCase]['content']];
                $fix = $phpcsFile->addFixableError($error, $nextCase, $type . 'NotLower', $data);
                if ($fix === \true) {
                    $phpcsFile->fixer->replaceToken($nextCase, $expected);
                }
            }
            if ($type === 'Case' && ($tokens[$nextCase + 1]['type'] !== 'T_WHITESPACE' || $tokens[$nextCase + 1]['content'] !== ' ')) {
                $error = 'CASE keyword must be followed by a single space';
                $fix = $phpcsFile->addFixableError($error, $nextCase, 'SpacingAfterCase');
                if ($fix === \true) {
                    if ($tokens[$nextCase + 1]['type'] !== 'T_WHITESPACE') {
                        $phpcsFile->fixer->addContent($nextCase, ' ');
                    } else {
                        $phpcsFile->fixer->replaceToken($nextCase + 1, ' ');
                    }
                }
            }
            $beforeCase = $phpcsFile->findPrevious(\T_WHITESPACE, $nextCase - 1, null, \true);
            if ($tokens[$beforeCase]['line'] === $tokens[$nextCase]['line']) {
                $error = '%s statement must be on a line by itself. Found content before';
                $fix = $phpcsFile->addFixableError($error, $nextCase, 'ContentBefore' . $type, [\strtoupper($type)]);
                if ($fix === \true) {
                    $padding = \str_repeat(' ', $caseAlignment - 1);
                    if ($tokens[$beforeCase + 1]['code'] === \T_WHITESPACE) {
                        $phpcsFile->fixer->replaceToken($beforeCase + 1, $phpcsFile->eolChar . $padding);
                    } else {
                        $phpcsFile->fixer->addContent($beforeCase, $phpcsFile->eolChar . $padding);
                    }
                }
            } elseif ($tokens[$nextCase]['column'] !== $caseAlignment) {
                $error = \strtoupper($type) . ' keyword must be indented ' . $this->indent . ' spaces from SWITCH keyword';
                $fix = $phpcsFile->addFixableError($error, $nextCase, $type . 'Indent');
                if ($fix === \true) {
                    $padding = \str_repeat(' ', $caseAlignment - 1);
                    if ($tokens[$nextCase]['column'] === 1 || $tokens[$nextCase - 1]['code'] !== \T_WHITESPACE) {
                        $phpcsFile->fixer->addContentBefore($nextCase, $padding);
                    } else {
                        $phpcsFile->fixer->replaceToken($nextCase - 1, $padding);
                    }
                }
            }
            if (isset($tokens[$nextCase]['scope_opener']) === \false) {
                // Parse error or live coding.
                continue;
            }
            $opener = $tokens[$nextCase]['scope_opener'];
            if ($tokens[$opener]['code'] === \T_CLOSE_TAG) {
                $error = 'There should be a colon before the PHP close tag to end the %s statement';
                $code = 'WrongOpener' . $type;
                $data = [\strtoupper($type)];
                $fix = $phpcsFile->addFixableError($error, $nextCase, $code, $data);
                if ($fix === \true) {
                    $prevNonEmpty = $phpcsFile->findPrevious(\T_WHITESPACE, $opener - 1, null, \true);
                    $phpcsFile->fixer->addContent($prevNonEmpty, ':');
                }
            }
            if ($tokens[$opener - 1]['type'] === 'T_WHITESPACE') {
                $error = 'There must be no space before the colon in a ' . \strtoupper($type) . ' statement';
                $fix = $phpcsFile->addFixableError($error, $nextCase, 'SpaceBeforeColon' . $type);
                if ($fix === \true) {
                    $phpcsFile->fixer->replaceToken($opener - 1, '');
                }
            }
            $nextBreak = $tokens[$nextCase]['scope_closer'];
            if ($tokens[$nextBreak]['code'] === \T_BREAK || $tokens[$nextBreak]['code'] === \T_RETURN || $tokens[$nextBreak]['code'] === \T_CONTINUE || $tokens[$nextBreak]['code'] === \T_THROW || $tokens[$nextBreak]['code'] === \T_EXIT || $tokens[$nextBreak]['code'] === \T_GOTO) {
                if ($tokens[$nextBreak]['scope_condition'] === $nextCase) {
                    // Only need to check a couple of things once, even if the
                    // break is shared between multiple case statements, or even
                    // the default case.
                    $beforeBreak = $phpcsFile->findPrevious(\T_WHITESPACE, $nextBreak - 1, null, \true);
                    if ($tokens[$beforeBreak]['line'] === $tokens[$nextBreak]['line']) {
                        $error = 'Case breaking statement must be on a line by itself. Found content before';
                        $fix = $phpcsFile->addFixableError($error, $nextBreak, 'ContentBeforeBreak');
                        if ($fix === \true) {
                            $padding = \str_repeat(' ', $caseAlignment - 1);
                            if ($tokens[$beforeBreak + 1]['code'] === \T_WHITESPACE) {
                                $phpcsFile->fixer->replaceToken($beforeBreak + 1, $phpcsFile->eolChar . $padding);
                            } else {
                                $phpcsFile->fixer->addContent($beforeBreak, $phpcsFile->eolChar . $padding);
                            }
                        }
                    } elseif ($tokens[$nextBreak]['column'] !== $caseAlignment) {
                        $error = 'Case breaking statement must be indented ' . $this->indent . ' spaces from SWITCH keyword';
                        $fix = $phpcsFile->addFixableError($error, $nextBreak, 'BreakIndent');
                        if ($fix === \true) {
                            $padding = \str_repeat(' ', $caseAlignment - 1);
                            if ($tokens[$nextBreak]['column'] === 1 || $tokens[$nextBreak - 1]['code'] !== \T_WHITESPACE) {
                                $phpcsFile->fixer->addContentBefore($nextBreak, $padding);
                            } else {
                                $phpcsFile->fixer->replaceToken($nextBreak - 1, $padding);
                            }
                        }
                    }
                    if ($tokens[$nextBreak]['line'] - $tokens[$beforeBreak]['line'] > 1) {
                        $error = 'Blank lines are not allowed before case breaking statements';
                        $phpcsFile->addError($error, $nextBreak, 'SpacingBeforeBreak');
                    }
                    // Figure out the relevant "next" line.
                    // Either the line containing the first non-empty content after the "break" statement
                    // (which may be the current line), or a line containing a comment,
                    // as long as it is not a trailing comment on the case statement line.
                    $nextLine = $tokens[$tokens[$stackPtr]['scope_closer']]['line'];
                    $semicolon = $phpcsFile->findEndOfStatement($nextBreak);
                    for ($nextRelevant = $semicolon + 1; $nextRelevant < $tokens[$stackPtr]['scope_closer']; $nextRelevant++) {
                        if (isset(Tokens::EMPTY_TOKENS[$tokens[$nextRelevant]['code']]) === \false) {
                            $nextLine = $tokens[$nextRelevant]['line'];
                            break;
                        }
                        if ($tokens[$nextRelevant]['code'] !== \T_WHITESPACE && $tokens[$nextBreak]['line'] !== $tokens[$nextRelevant]['line']) {
                            $nextLine = $tokens[$nextRelevant]['line'];
                            break;
                        }
                    }
                    if ($type === 'Case') {
                        // Ensure the BREAK statement is followed by
                        // a single blank line, or the end switch brace.
                        if ($nextLine !== $tokens[$semicolon]['line'] + 2 && $nextRelevant !== $tokens[$stackPtr]['scope_closer']) {
                            $error = 'Case breaking statements must be followed by a single blank line';
                            $fix = $phpcsFile->addFixableError($error, $nextBreak, 'SpacingAfterBreak');
                            if ($fix === \true) {
                                $padding = \str_repeat(' ', $caseAlignment - 1);
                                $phpcsFile->fixer->beginChangeset();
                                if ($nextLine === $tokens[$semicolon]['line']) {
                                    // Missing new line.
                                    $replacement = $phpcsFile->eolChar . $phpcsFile->eolChar . $padding;
                                    if ($tokens[$nextRelevant - 1]['code'] === \T_WHITESPACE) {
                                        $phpcsFile->fixer->replaceToken($nextRelevant - 1, $replacement);
                                    } else {
                                        $phpcsFile->fixer->addContentBefore($nextRelevant, $replacement);
                                    }
                                } else {
                                    // There is/are new line(s), just not the right number of them.
                                    for ($i = $semicolon + 1; $i < $nextRelevant; $i++) {
                                        if ($tokens[$semicolon]['line'] === $tokens[$i]['line']) {
                                            if ($tokens[$i]['line'] !== $tokens[$i + 1]['line']) {
                                                // Add extra new line at end of break line.
                                                $phpcsFile->fixer->addNewline($i);
                                            }
                                            continue;
                                        }
                                        if ($tokens[$i]['line'] === $tokens[$nextRelevant]['line']) {
                                            // Don't remove indentation on the line of the "next" content.
                                            break;
                                        }
                                        // In all other cases, remove whitespace.
                                        $phpcsFile->fixer->replaceToken($i, '');
                                    }
                                }
                                $phpcsFile->fixer->endChangeset();
                            }
                        }
                    } else {
                        // Ensure the BREAK statement is not followed by a blank line.
                        if ($nextLine - $tokens[$semicolon]['line'] > 1) {
                            $error = 'Blank lines are not allowed after the DEFAULT case\'s breaking statement';
                            $phpcsFile->addError($error, $nextBreak, 'SpacingAfterDefaultBreak');
                        }
                    }
                    // Figure out the relevant "next" line.
                    // Either the line containing the first non-empty content (which may be the current line),
                    // or a line containing a comment, as long as it is not a trailing comment on the case statement line.
                    $caseLine = $tokens[$nextCase]['line'];
                    $nextLine = $tokens[$nextBreak]['line'];
                    for ($i = $opener + 1; $i < $nextBreak; $i++) {
                        if (isset(Tokens::EMPTY_TOKENS[$tokens[$i]['code']]) === \false) {
                            $nextLine = $tokens[$i]['line'];
                            break;
                        }
                        if ($tokens[$i]['code'] !== \T_WHITESPACE && $tokens[$opener]['line'] !== $tokens[$i]['line']) {
                            $nextLine = $tokens[$i]['line'];
                            break;
                        }
                    }
                    if ($nextLine === $caseLine) {
                        $error = '%s statement must be on a line by itself. Found content after';
                        $fix = $phpcsFile->addFixableError($error, $opener, 'ContentAfter' . $type, [\strtoupper($type)]);
                        if ($fix === \true) {
                            $padding = \str_repeat(' ', $caseAlignment - 1 + $this->indent);
                            if ($tokens[$i - 1]['code'] === \T_WHITESPACE) {
                                $phpcsFile->fixer->replaceToken($i - 1, $phpcsFile->eolChar . $padding);
                            } else {
                                $phpcsFile->fixer->addContentBefore($i, $phpcsFile->eolChar . $padding);
                            }
                        }
                    } elseif ($nextLine - $caseLine > 1) {
                        $error = 'Blank lines are not allowed after ' . \strtoupper($type) . ' statements';
                        $phpcsFile->addError($error, $nextCase, 'SpacingAfter' . $type);
                    }
                }
                if ($tokens[$nextBreak]['code'] === \T_BREAK) {
                    if ($type === 'Case') {
                        // Ensure empty CASE statements are not allowed.
                        // They must have some code content in them. A comment is not enough.
                        // But count RETURN statements as valid content if they also
                        // happen to close the CASE statement.
                        $foundContent = \false;
                        for ($i = $tokens[$nextCase]['scope_opener'] + 1; $i < $nextBreak; $i++) {
                            if ($tokens[$i]['code'] === \T_CASE) {
                                $i = $tokens[$i]['scope_opener'];
                                continue;
                            }
                            if (isset(Tokens::EMPTY_TOKENS[$tokens[$i]['code']]) === \false) {
                                $foundContent = \true;
                                break;
                            }
                        }
                        if ($foundContent === \false) {
                            $error = 'Empty CASE statements are not allowed';
                            $phpcsFile->addError($error, $nextCase, 'EmptyCase');
                        }
                    } else {
                        // Ensure empty DEFAULT statements are not allowed.
                        // They must (at least) have a comment describing why
                        // the default case is being ignored.
                        $foundContent = \false;
                        for ($i = $tokens[$nextCase]['scope_opener'] + 1; $i < $nextBreak; $i++) {
                            if ($tokens[$i]['type'] !== 'T_WHITESPACE') {
                                $foundContent = \true;
                                break;
                            }
                        }
                        if ($foundContent === \false) {
                            $error = 'Comment required for empty DEFAULT case';
                            $phpcsFile->addError($error, $nextCase, 'EmptyDefault');
                        }
                    }
                }
            } elseif ($type === 'Default') {
                $error = 'DEFAULT case must have a breaking statement';
                $phpcsFile->addError($error, $nextCase, 'DefaultNoBreak');
            }
        }
        if ($foundDefault === \false) {
            $error = 'All SWITCH statements must contain a DEFAULT case';
            $phpcsFile->addError($error, $stackPtr, 'MissingDefault');
        }
        if ($tokens[$switch['scope_closer']]['column'] !== $switch['column']) {
            $error = 'Closing brace of SWITCH statement must be aligned with SWITCH keyword';
            $phpcsFile->addError($error, $switch['scope_closer'], 'CloseBraceAlign');
        }
        if ($caseCount === 0) {
            $error = 'SWITCH statements must contain at least one CASE statement';
            $phpcsFile->addError($error, $stackPtr, 'MissingCase');
        }
    }
}
