<?php

/**
 * The base tokenizer class.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Tokenizers;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Exceptions\TokenizerException;
use PHP_CodeSniffer\Util\Common;
use PHP_CodeSniffer\Util\IgnoreList;
use PHP_CodeSniffer\Util\Tokens;
use PHP_CodeSniffer\Util\Writers\StatusWriter;
abstract class Tokenizer
{
    /**
     * List of tokens which may contain tab characters.
     *
     * @var array<int|string, true>
     */
    private const TOKENS_WITH_TABS = [\T_WHITESPACE => \true, \T_COMMENT => \true, \T_DOC_COMMENT => \true, \T_DOC_COMMENT_WHITESPACE => \true, \T_DOC_COMMENT_STRING => \true, \T_CONSTANT_ENCAPSED_STRING => \true, \T_DOUBLE_QUOTED_STRING => \true, \T_START_HEREDOC => \true, \T_START_NOWDOC => \true, \T_HEREDOC => \true, \T_NOWDOC => \true, \T_END_HEREDOC => \true, \T_END_NOWDOC => \true, \T_INLINE_HTML => \true, \T_YIELD_FROM => \true];
    /**
     * The config data for the run.
     *
     * @var \PHP_CodeSniffer\Config
     */
    protected $config = null;
    /**
     * The EOL char used in the content.
     *
     * @var string
     */
    protected $eolChar = '';
    /**
     * A token-based representation of the content.
     *
     * @var array
     */
    protected $tokens = [];
    /**
     * The number of tokens in the tokens array.
     *
     * @var integer
     */
    protected $numTokens = 0;
    /**
     * A list of tokens that are allowed to open a scope.
     *
     * @var array
     */
    public $scopeOpeners = [];
    /**
     * A list of tokens that end the scope.
     *
     * @var array<int|string, int|string>
     */
    public $endScopeTokens = [];
    /**
     * Known lengths of tokens.
     *
     * @var array<string|int, int>
     */
    public $knownLengths = [];
    /**
     * A list of lines being ignored due to error suppression comments.
     *
     * @var array
     */
    public $ignoredLines = [];
    /**
     * Initialise and run the tokenizer.
     *
     * @param string                       $content The content to tokenize.
     * @param \PHP_CodeSniffer\Config|null $config  The config data for the run.
     * @param string                       $eolChar The EOL char used in the content.
     *
     * @return void
     * @throws \PHP_CodeSniffer\Exceptions\TokenizerException If the file appears to be minified.
     */
    public function __construct(string $content, ?Config $config, string $eolChar = '\\n')
    {
        $this->eolChar = $eolChar;
        $this->config = $config;
        $this->tokens = $this->tokenize($content);
        if ($config === null) {
            return;
        }
        $this->createPositionMap();
        $this->createTokenMap();
        $this->createParenthesisNestingMap();
        $this->createScopeMap();
        $this->createLevelMap();
        // Allow the tokenizer to do additional processing if required.
        $this->processAdditional();
    }
    /**
     * Checks the content to see if it looks minified.
     *
     * @param string $content The content to tokenize.
     * @param string $eolChar The EOL char used in the content.
     *
     * @return boolean
     */
    protected function isMinifiedContent(string $content, string $eolChar = '\\n')
    {
        // Minified files often have a very large number of characters per line
        // and cause issues when tokenizing.
        $numChars = \strlen($content);
        $numLines = \substr_count($content, $eolChar) + 1;
        $average = $numChars / $numLines;
        if ($average > 100) {
            return \true;
        }
        return \false;
    }
    /**
     * Gets the array of tokens.
     *
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }
    /**
     * Creates an array of tokens when given some content.
     *
     * @param string $code The code to tokenize.
     *
     * @return array
     */
    protected abstract function tokenize(string $code);
    /**
     * Performs additional processing after main tokenizing.
     *
     * @return void
     */
    protected abstract function processAdditional();
    /**
     * Sets token position information.
     *
     * Can also convert tabs into spaces. Each tab can represent between
     * 1 and $width spaces, so this cannot be a straight string replace.
     *
     * @return void
     */
    private function createPositionMap()
    {
        $currColumn = 1;
        $lineNumber = 1;
        $eolLen = \strlen($this->eolChar);
        $ignoring = null;
        $ignoreAll = IgnoreList::getInstanceIgnoringAll();
        $inTests = \defined('PHP_CODESNIFFER_IN_TESTS');
        $checkEncoding = \false;
        if (\function_exists('iconv_strlen') === \true) {
            $checkEncoding = \true;
        }
        $checkAnnotations = $this->config->annotations;
        $encoding = $this->config->encoding;
        $tabWidth = $this->config->tabWidth;
        $this->numTokens = \count($this->tokens);
        for ($i = 0; $i < $this->numTokens; $i++) {
            $this->tokens[$i]['line'] = $lineNumber;
            $this->tokens[$i]['column'] = $currColumn;
            if (isset($this->knownLengths[$this->tokens[$i]['code']]) === \true) {
                // There are no tabs in the tokens we know the length of.
                $length = $this->knownLengths[$this->tokens[$i]['code']];
                $currColumn += $length;
            } elseif ($tabWidth === 0 || isset(self::TOKENS_WITH_TABS[$this->tokens[$i]['code']]) === \false || \strpos($this->tokens[$i]['content'], "\t") === \false) {
                // There are no tabs in this content, or we aren't replacing them.
                if ($checkEncoding === \true) {
                    // Not using the default encoding, so take a bit more care.
                    $oldLevel = \error_reporting();
                    \error_reporting(0);
                    $length = \iconv_strlen($this->tokens[$i]['content'], $encoding);
                    \error_reporting($oldLevel);
                    if ($length === \false) {
                        // String contained invalid characters, so revert to default.
                        $length = \strlen($this->tokens[$i]['content']);
                    }
                } else {
                    $length = \strlen($this->tokens[$i]['content']);
                }
                $currColumn += $length;
            } else {
                $this->replaceTabsInToken($this->tokens[$i]);
                $length = $this->tokens[$i]['length'];
                $currColumn += $length;
            }
            $this->tokens[$i]['length'] = $length;
            if (isset($this->knownLengths[$this->tokens[$i]['code']]) === \false && \strpos($this->tokens[$i]['content'], $this->eolChar) !== \false) {
                $lineNumber++;
                $currColumn = 1;
                // Newline chars are not counted in the token length.
                $this->tokens[$i]['length'] -= $eolLen;
            }
            if ($this->tokens[$i]['code'] === \T_COMMENT || $this->tokens[$i]['code'] === \T_DOC_COMMENT_STRING || $this->tokens[$i]['code'] === \T_DOC_COMMENT_TAG || $inTests === \true && $this->tokens[$i]['code'] === \T_INLINE_HTML) {
                $commentText = \ltrim($this->tokens[$i]['content'], " \t/*#");
                $commentText = \rtrim($commentText, " */\t\r\n");
                $commentTextLower = \strtolower($commentText);
                if (\substr($commentTextLower, 0, 6) === 'phpcs:' || \substr($commentTextLower, 0, 7) === '@phpcs:') {
                    // If the @phpcs: syntax is being used, strip the @ to make
                    // comparisons easier.
                    if ($commentText[0] === '@') {
                        $commentText = (string) \substr($commentText, 1);
                        $commentTextLower = \strtolower($commentText);
                    }
                    // If there is a comment on the end, strip it off.
                    $commentStart = \strpos($commentTextLower, ' --');
                    if ($commentStart !== \false) {
                        $commentText = (string) \substr($commentText, 0, $commentStart);
                        $commentTextLower = \strtolower($commentText);
                    }
                    // If this comment is the only thing on the line, it tells us
                    // to ignore the following line. If the line contains other content
                    // then we are just ignoring this one single line.
                    $lineHasOtherContent = \false;
                    $lineHasOtherTokens = \false;
                    if ($i > 0) {
                        for ($prev = $i - 1; $prev > 0; $prev--) {
                            if ($this->tokens[$prev]['line'] !== $this->tokens[$i]['line']) {
                                // Changed lines.
                                break;
                            }
                            if ($this->tokens[$prev]['code'] === \T_WHITESPACE || $this->tokens[$prev]['code'] === \T_DOC_COMMENT_WHITESPACE || $this->tokens[$prev]['code'] === \T_INLINE_HTML && \trim($this->tokens[$prev]['content']) === '') {
                                continue;
                            }
                            $lineHasOtherTokens = \true;
                            if ($this->tokens[$prev]['code'] === \T_OPEN_TAG || $this->tokens[$prev]['code'] === \T_DOC_COMMENT_STAR) {
                                continue;
                            }
                            $lineHasOtherContent = \true;
                            break;
                        }
                        $changedLines = \false;
                        for ($next = $i; $next < $this->numTokens; $next++) {
                            if ($changedLines === \true) {
                                // Changed lines.
                                break;
                            }
                            if (isset($this->knownLengths[$this->tokens[$next]['code']]) === \false && \strpos($this->tokens[$next]['content'], $this->eolChar) !== \false) {
                                // Last token on the current line.
                                $changedLines = \true;
                            }
                            if ($next === $i) {
                                continue;
                            }
                            if ($this->tokens[$next]['code'] === \T_WHITESPACE || $this->tokens[$next]['code'] === \T_DOC_COMMENT_WHITESPACE || $this->tokens[$next]['code'] === \T_INLINE_HTML && \trim($this->tokens[$next]['content']) === '') {
                                continue;
                            }
                            $lineHasOtherTokens = \true;
                            if ($this->tokens[$next]['code'] === \T_CLOSE_TAG) {
                                continue;
                            }
                            $lineHasOtherContent = \true;
                            break;
                        }
                    }
                    if (\substr($commentTextLower, 0, 9) === 'phpcs:set') {
                        // Ignore standards for complete lines that change sniff settings.
                        if ($lineHasOtherTokens === \false) {
                            $this->ignoredLines[$this->tokens[$i]['line']] = $ignoreAll;
                        }
                        // Need to maintain case here, to get the correct sniff code.
                        $parts = \explode(' ', (string) \substr($commentText, 10));
                        if (\count($parts) >= 2) {
                            $sniffParts = \explode('.', $parts[0]);
                            if (\count($sniffParts) >= 3) {
                                $this->tokens[$i]['sniffCode'] = \array_shift($parts);
                                $this->tokens[$i]['sniffProperty'] = \array_shift($parts);
                                $this->tokens[$i]['sniffPropertyValue'] = \rtrim(\implode(' ', $parts), " */\r\n");
                            }
                        }
                        $this->tokens[$i]['code'] = \T_PHPCS_SET;
                        $this->tokens[$i]['type'] = 'T_PHPCS_SET';
                    } elseif (\substr($commentTextLower, 0, 16) === 'phpcs:ignorefile') {
                        // The whole file will be ignored, but at least set the correct token.
                        $this->tokens[$i]['code'] = \T_PHPCS_IGNORE_FILE;
                        $this->tokens[$i]['type'] = 'T_PHPCS_IGNORE_FILE';
                    } elseif (\substr($commentTextLower, 0, 13) === 'phpcs:disable') {
                        if ($lineHasOtherContent === \false) {
                            // Completely ignore the comment line.
                            $this->ignoredLines[$this->tokens[$i]['line']] = $ignoreAll;
                        }
                        $disabledSniffs = [];
                        $additionalText = (string) \substr($commentText, 14);
                        if (empty($additionalText) === \true) {
                            $ignoring = $ignoreAll;
                        } else {
                            $ignoring = IgnoreList::getNewInstanceFrom($ignoring);
                            $parts = \explode(',', $additionalText);
                            foreach ($parts as $sniffCode) {
                                $sniffCode = \trim($sniffCode);
                                $disabledSniffs[$sniffCode] = \true;
                                $ignoring->set($sniffCode, \true);
                            }
                        }
                        $this->tokens[$i]['code'] = \T_PHPCS_DISABLE;
                        $this->tokens[$i]['type'] = 'T_PHPCS_DISABLE';
                        $this->tokens[$i]['sniffCodes'] = $disabledSniffs;
                    } elseif (\substr($commentTextLower, 0, 12) === 'phpcs:enable') {
                        if ($ignoring !== null) {
                            $enabledSniffs = [];
                            $additionalText = (string) \substr($commentText, 13);
                            if (empty($additionalText) === \true) {
                                $ignoring = null;
                            } else {
                                $ignoring = IgnoreList::getNewInstanceFrom($ignoring);
                                $parts = \explode(',', $additionalText);
                                foreach ($parts as $sniffCode) {
                                    $sniffCode = \trim($sniffCode);
                                    $enabledSniffs[$sniffCode] = \true;
                                    $ignoring->set($sniffCode, \false);
                                }
                                if ($ignoring->ignoresNothing() === \true) {
                                    $ignoring = null;
                                }
                            }
                            if ($lineHasOtherContent === \false) {
                                // Completely ignore the comment line.
                                $this->ignoredLines[$this->tokens[$i]['line']] = $ignoreAll;
                            } else {
                                // The comment is on the same line as the code it is ignoring,
                                // so respect the new ignore rules.
                                $this->ignoredLines[$this->tokens[$i]['line']] = $ignoring;
                            }
                            $this->tokens[$i]['sniffCodes'] = $enabledSniffs;
                        }
                        $this->tokens[$i]['code'] = \T_PHPCS_ENABLE;
                        $this->tokens[$i]['type'] = 'T_PHPCS_ENABLE';
                    } elseif (\substr($commentTextLower, 0, 12) === 'phpcs:ignore') {
                        $ignoreRules = [];
                        $additionalText = (string) \substr($commentText, 13);
                        if (empty($additionalText) === \true) {
                            $ignoreRules = ['.all' => \true];
                            $lineIgnoring = $ignoreAll;
                        } else {
                            $parts = \explode(',', $additionalText);
                            $lineIgnoring = IgnoreList::getNewInstanceFrom($ignoring);
                            foreach ($parts as $sniffCode) {
                                $ignoreRules[\trim($sniffCode)] = \true;
                                $lineIgnoring->set($sniffCode, \true);
                            }
                        }
                        $this->tokens[$i]['code'] = \T_PHPCS_IGNORE;
                        $this->tokens[$i]['type'] = 'T_PHPCS_IGNORE';
                        $this->tokens[$i]['sniffCodes'] = $ignoreRules;
                        if ($lineHasOtherContent === \false) {
                            // Completely ignore the comment line, and set the following
                            // line to include the ignore rules we've set.
                            $this->ignoredLines[$this->tokens[$i]['line']] = $ignoreAll;
                            $this->ignoredLines[$this->tokens[$i]['line'] + 1] = $lineIgnoring;
                        } else {
                            // The comment is on the same line as the code it is ignoring,
                            // so respect the ignore rules it set.
                            $this->ignoredLines[$this->tokens[$i]['line']] = $lineIgnoring;
                        }
                    }
                }
            }
            if ($ignoring !== null && isset($this->ignoredLines[$this->tokens[$i]['line']]) === \false) {
                $this->ignoredLines[$this->tokens[$i]['line']] = $ignoring;
            }
        }
        // If annotations are being ignored, we clear out all the ignore rules
        // but leave the annotations tokenized as normal.
        if ($checkAnnotations === \false) {
            $this->ignoredLines = [];
        }
    }
    /**
     * Replaces tabs in original token content with spaces.
     *
     * Each tab can represent between 1 and $config->tabWidth spaces,
     * so this cannot be a straight string replace. The original content
     * is placed into an orig_content index and the new token length is also
     * set in the length index.
     *
     * @param array    $token    The token to replace tabs inside.
     * @param string   $prefix   The character to use to represent the start of a tab.
     * @param string   $padding  The character to use to represent the end of a tab.
     * @param int|null $tabWidth The number of spaces each tab represents.
     *
     * @return void
     */
    public function replaceTabsInToken(array &$token, string $prefix = ' ', string $padding = ' ', ?int $tabWidth = null)
    {
        $checkEncoding = \false;
        if (\function_exists('iconv_strlen') === \true) {
            $checkEncoding = \true;
        }
        $currColumn = $token['column'];
        if ($tabWidth === null) {
            $tabWidth = $this->config->tabWidth;
            if ($tabWidth === 0) {
                $tabWidth = 1;
            }
        }
        if (\rtrim($token['content'], "\t") === '') {
            // String only contains tabs, so we can shortcut the process.
            $numTabs = \strlen($token['content']);
            $firstTabSize = $tabWidth - ($currColumn - 1) % $tabWidth;
            $length = $firstTabSize + $tabWidth * ($numTabs - 1);
            $newContent = $prefix . \str_repeat($padding, $length - 1);
        } else {
            // We need to determine the length of each tab.
            $tabs = \explode("\t", $token['content']);
            $numTabs = \count($tabs) - 1;
            $tabNum = 0;
            $newContent = '';
            $length = 0;
            foreach ($tabs as $content) {
                if ($content !== '') {
                    $newContent .= $content;
                    if ($checkEncoding === \true) {
                        // Not using ASCII encoding, so take a bit more care.
                        $oldLevel = \error_reporting();
                        \error_reporting(0);
                        $contentLength = \iconv_strlen($content, $this->config->encoding);
                        \error_reporting($oldLevel);
                        if ($contentLength === \false) {
                            // String contained invalid characters, so revert to default.
                            $contentLength = \strlen($content);
                        }
                    } else {
                        $contentLength = \strlen($content);
                    }
                    $currColumn += $contentLength;
                    $length += $contentLength;
                }
                // The last piece of content does not have a tab after it.
                if ($tabNum === $numTabs) {
                    break;
                }
                // Process the tab that comes after the content.
                $tabNum++;
                // Move the pointer to the next tab stop.
                $pad = $tabWidth - ($currColumn + $tabWidth - 1) % $tabWidth;
                $currColumn += $pad;
                $length += $pad;
                $newContent .= $prefix . \str_repeat($padding, $pad - 1);
            }
        }
        $token['orig_content'] = $token['content'];
        $token['content'] = $newContent;
        $token['length'] = $length;
    }
    /**
     * Creates a map of brackets positions.
     *
     * @return void
     */
    private function createTokenMap()
    {
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            StatusWriter::write('*** START TOKEN MAP ***', 1);
        }
        $squareOpeners = [];
        $curlyOpeners = [];
        $this->numTokens = \count($this->tokens);
        $openers = [];
        for ($i = 0; $i < $this->numTokens; $i++) {
            /*
                Parenthesis mapping.
            */
            if (isset(Tokens::PARENTHESIS_OPENERS[$this->tokens[$i]['code']]) === \true) {
                // Find the next non-empty token.
                $find = Tokens::EMPTY_TOKENS;
                if ($this->tokens[$i]['code'] === \T_FUNCTION) {
                    $find[\T_STRING] = \T_STRING;
                    $find[\T_BITWISE_AND] = \T_BITWISE_AND;
                }
                for ($j = $i + 1; isset($this->tokens[$j], $find[$this->tokens[$j]['code']]) === \true; $j++) {
                }
                if ($j < $this->numTokens && $this->tokens[$j]['code'] === \T_OPEN_PARENTHESIS) {
                    $openers[] = $j;
                    $this->tokens[$i]['parenthesis_opener'] = $j;
                    $this->tokens[$i]['parenthesis_closer'] = null;
                    $this->tokens[$i]['parenthesis_owner'] = $i;
                    $this->tokens[$j]['parenthesis_opener'] = $j;
                    $this->tokens[$j]['parenthesis_owner'] = $i;
                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        StatusWriter::write("=> Found parenthesis owner at {$i}", \count($openers) + 1);
                        StatusWriter::write("=> Found parenthesis opener at {$j} for {$i}", \count($openers));
                    }
                    $i = $j;
                }
            } elseif ($this->tokens[$i]['code'] === \T_OPEN_PARENTHESIS) {
                $openers[] = $i;
                $this->tokens[$i]['parenthesis_opener'] = $i;
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    StatusWriter::write("=> Found unowned parenthesis opener at {$i}", \count($openers));
                }
            } elseif ($this->tokens[$i]['code'] === \T_CLOSE_PARENTHESIS) {
                // Did we set an owner for this set of parenthesis?
                $numOpeners = \count($openers);
                if ($numOpeners !== 0) {
                    $opener = \array_pop($openers);
                    if (isset($this->tokens[$opener]['parenthesis_owner']) === \true) {
                        $owner = $this->tokens[$opener]['parenthesis_owner'];
                        $this->tokens[$owner]['parenthesis_closer'] = $i;
                        $this->tokens[$i]['parenthesis_owner'] = $owner;
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            StatusWriter::write("=> Found parenthesis closer at {$i} for {$owner}", \count($openers) + 1);
                        }
                    } elseif (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        StatusWriter::write("=> Found unowned parenthesis closer at {$i} for {$opener}", \count($openers) + 1);
                    }
                    $this->tokens[$i]['parenthesis_opener'] = $opener;
                    $this->tokens[$i]['parenthesis_closer'] = $i;
                    $this->tokens[$opener]['parenthesis_closer'] = $i;
                }
            } elseif ($this->tokens[$i]['code'] === \T_ATTRIBUTE) {
                $openers[] = $i;
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    StatusWriter::write("=> Found attribute opener at {$i}", \count($openers));
                }
                $this->tokens[$i]['attribute_opener'] = $i;
                $this->tokens[$i]['attribute_closer'] = null;
            } elseif ($this->tokens[$i]['code'] === \T_ATTRIBUTE_END) {
                $numOpeners = \count($openers);
                if ($numOpeners !== 0) {
                    $opener = \array_pop($openers);
                    if (isset($this->tokens[$opener]['attribute_opener']) === \true) {
                        $this->tokens[$opener]['attribute_closer'] = $i;
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            StatusWriter::write("=> Found attribute closer at {$i} for {$opener}", \count($openers) + 1);
                        }
                        for ($x = $opener + 1; $x <= $i; ++$x) {
                            if (isset($this->tokens[$x]['attribute_closer']) === \true) {
                                continue;
                            }
                            $this->tokens[$x]['attribute_opener'] = $opener;
                            $this->tokens[$x]['attribute_closer'] = $i;
                        }
                    } elseif (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        StatusWriter::write("=> Found unowned attribute closer at {$i} for {$opener}", \count($openers) + 1);
                    }
                }
            }
            /*
                Bracket mapping.
            */
            switch ($this->tokens[$i]['code']) {
                case \T_OPEN_SQUARE_BRACKET:
                    $squareOpeners[] = $i;
                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        StatusWriter::write("=> Found square bracket opener at {$i}", \count($squareOpeners) + \count($curlyOpeners));
                    }
                    break;
                case \T_OPEN_CURLY_BRACKET:
                    if (isset($this->tokens[$i]['scope_closer']) === \false) {
                        $curlyOpeners[] = $i;
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            StatusWriter::write("=> Found curly bracket opener at {$i}", \count($squareOpeners) + \count($curlyOpeners));
                        }
                    }
                    break;
                case \T_CLOSE_SQUARE_BRACKET:
                    if (empty($squareOpeners) === \false) {
                        $opener = \array_pop($squareOpeners);
                        $this->tokens[$i]['bracket_opener'] = $opener;
                        $this->tokens[$i]['bracket_closer'] = $i;
                        $this->tokens[$opener]['bracket_opener'] = $opener;
                        $this->tokens[$opener]['bracket_closer'] = $i;
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            StatusWriter::write("=> Found square bracket closer at {$i} for {$opener}", \count($squareOpeners) + \count($curlyOpeners) + 1);
                        }
                    }
                    break;
                case \T_CLOSE_CURLY_BRACKET:
                    if (empty($curlyOpeners) === \false && isset($this->tokens[$i]['scope_opener']) === \false) {
                        $opener = \array_pop($curlyOpeners);
                        $this->tokens[$i]['bracket_opener'] = $opener;
                        $this->tokens[$i]['bracket_closer'] = $i;
                        $this->tokens[$opener]['bracket_opener'] = $opener;
                        $this->tokens[$opener]['bracket_closer'] = $i;
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            StatusWriter::write("=> Found curly bracket closer at {$i} for {$opener}", \count($squareOpeners) + \count($curlyOpeners) + 1);
                        }
                    }
                    break;
                default:
                    continue 2;
            }
        }
        // Cleanup for any openers that we didn't find closers for.
        // This typically means there was a syntax error breaking things.
        foreach ($openers as $opener) {
            unset($this->tokens[$opener]['parenthesis_opener']);
            unset($this->tokens[$opener]['parenthesis_owner']);
        }
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            StatusWriter::write('*** END TOKEN MAP ***', 1);
        }
    }
    /**
     * Creates a map for the parenthesis tokens that surround other tokens.
     *
     * @return void
     */
    private function createParenthesisNestingMap()
    {
        $map = [];
        for ($i = 0; $i < $this->numTokens; $i++) {
            if (isset($this->tokens[$i]['parenthesis_opener']) === \true && $i === $this->tokens[$i]['parenthesis_opener']) {
                if (empty($map) === \false) {
                    $this->tokens[$i]['nested_parenthesis'] = $map;
                }
                if (isset($this->tokens[$i]['parenthesis_closer']) === \true) {
                    $map[$this->tokens[$i]['parenthesis_opener']] = $this->tokens[$i]['parenthesis_closer'];
                }
            } elseif (isset($this->tokens[$i]['parenthesis_closer']) === \true && $i === $this->tokens[$i]['parenthesis_closer']) {
                \array_pop($map);
                if (empty($map) === \false) {
                    $this->tokens[$i]['nested_parenthesis'] = $map;
                }
            } else {
                if (empty($map) === \false) {
                    $this->tokens[$i]['nested_parenthesis'] = $map;
                }
            }
        }
    }
    /**
     * Creates a scope map of tokens that open scopes.
     *
     * @return void
     * @see    recurseScopeMap()
     */
    private function createScopeMap()
    {
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            StatusWriter::write('*** START SCOPE MAP ***', 1);
        }
        for ($i = 0; $i < $this->numTokens; $i++) {
            // Check to see if the current token starts a new scope.
            if (isset($this->scopeOpeners[$this->tokens[$i]['code']]) === \true) {
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    $type = $this->tokens[$i]['type'];
                    $content = Common::prepareForOutput($this->tokens[$i]['content']);
                    StatusWriter::write("Start scope map at {$i}:{$type} => {$content}", 1);
                }
                if (isset($this->tokens[$i]['scope_condition']) === \true) {
                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        StatusWriter::write('* already processed, skipping *', 1);
                    }
                    continue;
                }
                $i = $this->recurseScopeMap($i);
            }
        }
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            StatusWriter::write('*** END SCOPE MAP ***', 1);
        }
    }
    /**
     * Recurses though the scope openers to build a scope map.
     *
     * @param int $stackPtr The position in the stack of the token that
     *                      opened the scope (eg. an IF token or FOR token).
     * @param int $depth    How many scope levels down we are.
     * @param int $ignore   How many curly braces we are ignoring.
     *
     * @return int The position in the stack that closed the scope.
     * @throws \PHP_CodeSniffer\Exceptions\TokenizerException If the nesting level gets too deep.
     */
    private function recurseScopeMap(int $stackPtr, int $depth = 1, int &$ignore = 0)
    {
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            StatusWriter::write("=> Begin scope map recursion at token {$stackPtr} with depth {$depth}", $depth);
        }
        $opener = null;
        $currType = $this->tokens[$stackPtr]['code'];
        $startLine = $this->tokens[$stackPtr]['line'];
        // We will need this to restore the value if we end up
        // returning a token ID that causes our calling function to go back
        // over already ignored braces.
        $originalIgnore = $ignore;
        // If the start token for this scope opener is the same as
        // the scope token, we have already found our opener.
        if (isset($this->scopeOpeners[$currType]['start'][$currType]) === \true) {
            $opener = $stackPtr;
        }
        for ($i = $stackPtr + 1; $i < $this->numTokens; $i++) {
            $tokenType = $this->tokens[$i]['code'];
            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                $type = $this->tokens[$i]['type'];
                $line = $this->tokens[$i]['line'];
                $content = Common::prepareForOutput($this->tokens[$i]['content']);
                $statusMessage = "Process token {$i} on line {$line} [";
                if ($opener !== null) {
                    $statusMessage .= "opener:{$opener};";
                }
                if ($ignore > 0) {
                    $statusMessage .= "ignore={$ignore};";
                }
                $statusMessage .= "]: {$type} => {$content}";
                StatusWriter::write($statusMessage, $depth);
            }
            // Very special case for IF statements in PHP that can be defined without
            // scope tokens. E.g., if (1) 1; 1 ? (1 ? 1 : 1) : 1;
            // If an IF statement below this one has an opener but no
            // keyword, the opener will be incorrectly assigned to this IF statement.
            // The same case also applies to USE statements, which don't have to have
            // openers, so a following USE statement can cause an incorrect brace match.
            if (($currType === \T_IF || $currType === \T_ELSE || $currType === \T_USE) && $opener === null && ($this->tokens[$i]['code'] === \T_SEMICOLON || $this->tokens[$i]['code'] === \T_CLOSE_TAG)) {
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    $type = $this->tokens[$stackPtr]['type'];
                    if ($this->tokens[$i]['code'] === \T_SEMICOLON) {
                        $closerType = 'semicolon';
                    } else {
                        $closerType = 'close tag';
                    }
                    StatusWriter::write("=> Found {$closerType} before scope opener for {$stackPtr}:{$type}, bailing", $depth);
                }
                return $i;
            }
            // Special case for PHP control structures that have no braces.
            // If we find a curly brace closer before we find the opener,
            // we're not going to find an opener. That closer probably belongs to
            // a control structure higher up.
            if ($opener === null && $ignore === 0 && $tokenType === \T_CLOSE_CURLY_BRACKET && isset($this->scopeOpeners[$currType]['end'][$tokenType]) === \true) {
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    $type = $this->tokens[$stackPtr]['type'];
                    StatusWriter::write("=> Found curly brace closer before scope opener for {$stackPtr}:{$type}, bailing", $depth);
                }
                return $i - 1;
            }
            if ($opener !== null && (isset($this->tokens[$i]['scope_opener']) === \false || $this->scopeOpeners[$this->tokens[$stackPtr]['code']]['shared'] === \true) && isset($this->scopeOpeners[$currType]['end'][$tokenType]) === \true) {
                if ($ignore > 0 && $tokenType === \T_CLOSE_CURLY_BRACKET) {
                    // The last opening bracket must have been for a string
                    // offset or alike, so let's ignore it.
                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        StatusWriter::write('* finished ignoring curly brace *', $depth);
                    }
                    $ignore--;
                    continue;
                } elseif ($this->tokens[$opener]['code'] === \T_OPEN_CURLY_BRACKET && $tokenType !== \T_CLOSE_CURLY_BRACKET) {
                    // The opener is a curly bracket so the closer must be a curly bracket as well.
                    // We ignore this closer to handle cases such as T_ELSE or T_ELSEIF being considered
                    // a closer of T_IF when it should not.
                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        $type = $this->tokens[$stackPtr]['type'];
                        StatusWriter::write("=> Ignoring non-curly scope closer for {$stackPtr}:{$type}", $depth);
                    }
                } else {
                    $scopeCloser = $i;
                    $todo = [$stackPtr, $opener];
                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        $type = $this->tokens[$stackPtr]['type'];
                        $closerType = $this->tokens[$scopeCloser]['type'];
                        StatusWriter::write("=> Found scope closer ({$scopeCloser}:{$closerType}) for {$stackPtr}:{$type}", $depth);
                    }
                    $validCloser = \true;
                    if (($this->tokens[$stackPtr]['code'] === \T_IF || $this->tokens[$stackPtr]['code'] === \T_ELSEIF) && ($tokenType === \T_ELSE || $tokenType === \T_ELSEIF)) {
                        // To be a closer, this token must have an opener.
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            StatusWriter::write('* closer needs to be tested *', $depth);
                        }
                        $i = self::recurseScopeMap($i, $depth + 1, $ignore);
                        if (isset($this->tokens[$scopeCloser]['scope_opener']) === \false) {
                            $validCloser = \false;
                            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                                StatusWriter::write('* closer is not valid (no opener found) *', $depth);
                            }
                        } elseif ($this->tokens[$this->tokens[$scopeCloser]['scope_opener']]['code'] !== $this->tokens[$opener]['code']) {
                            $validCloser = \false;
                            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                                $type = $this->tokens[$this->tokens[$scopeCloser]['scope_opener']]['type'];
                                $openerType = $this->tokens[$opener]['type'];
                                StatusWriter::write("* closer is not valid (mismatched opener type; {$type} != {$openerType}) *", $depth);
                            }
                        } elseif (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            StatusWriter::write('* closer was valid *', $depth);
                        }
                    } else {
                        // The closer was not processed, so we need to
                        // complete that token as well.
                        $todo[] = $scopeCloser;
                    }
                    if ($validCloser === \true) {
                        foreach ($todo as $token) {
                            $this->tokens[$token]['scope_condition'] = $stackPtr;
                            $this->tokens[$token]['scope_opener'] = $opener;
                            $this->tokens[$token]['scope_closer'] = $scopeCloser;
                        }
                        if ($this->scopeOpeners[$this->tokens[$stackPtr]['code']]['shared'] === \true) {
                            // As we are going back to where we started originally, restore
                            // the ignore value back to its original value.
                            $ignore = $originalIgnore;
                            return $opener;
                        } elseif ($scopeCloser === $i && isset($this->scopeOpeners[$tokenType]) === \true) {
                            // Unset scope_condition here or else the token will appear to have
                            // already been processed, and it will be skipped. Normally we want that,
                            // but in this case, the token is both a closer and an opener, so
                            // it needs to act like an opener. This is also why we return the
                            // token before this one; so the closer has a chance to be processed
                            // a second time, but as an opener.
                            unset($this->tokens[$scopeCloser]['scope_condition']);
                            return $i - 1;
                        } else {
                            return $i;
                        }
                    } else {
                        continue;
                    }
                }
            }
            // Is this an opening condition ?
            if (isset($this->scopeOpeners[$tokenType]) === \true) {
                if ($opener === null) {
                    if ($tokenType === \T_USE) {
                        // PHP use keywords are special because they can be
                        // used as blocks but also inline in function definitions.
                        // So if we find them nested inside another opener, just skip them.
                        continue;
                    }
                    if ($tokenType === \T_FUNCTION && $this->tokens[$stackPtr]['code'] !== \T_FUNCTION) {
                        // Probably a closure, so process it manually.
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            $type = $this->tokens[$stackPtr]['type'];
                            StatusWriter::write("=> Found function before scope opener for {$stackPtr}:{$type}, processing manually", $depth);
                        }
                        if (isset($this->tokens[$i]['scope_closer']) === \true) {
                            // We've already processed this closure.
                            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                                StatusWriter::write('* already processed, skipping *', $depth);
                            }
                            $i = $this->tokens[$i]['scope_closer'];
                            continue;
                        }
                        $i = self::recurseScopeMap($i, $depth + 1, $ignore);
                        continue;
                    }
                    if ($tokenType === \T_CLASS) {
                        // Probably an anonymous class inside another anonymous class,
                        // so process it manually.
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            $type = $this->tokens[$stackPtr]['type'];
                            StatusWriter::write("=> Found class before scope opener for {$stackPtr}:{$type}, processing manually", $depth);
                        }
                        if (isset($this->tokens[$i]['scope_closer']) === \true) {
                            // We've already processed this anon class.
                            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                                StatusWriter::write('* already processed, skipping *', $depth);
                            }
                            $i = $this->tokens[$i]['scope_closer'];
                            continue;
                        }
                        $i = self::recurseScopeMap($i, $depth + 1, $ignore);
                        continue;
                    }
                    // Found another opening condition but still haven't
                    // found our opener, so we are never going to find one.
                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        $type = $this->tokens[$stackPtr]['type'];
                        StatusWriter::write("=> Found new opening condition before scope opener for {$stackPtr}:{$type}, ", $depth, 0);
                    }
                    if (($this->tokens[$stackPtr]['code'] === \T_IF || $this->tokens[$stackPtr]['code'] === \T_ELSEIF || $this->tokens[$stackPtr]['code'] === \T_ELSE) && ($this->tokens[$i]['code'] === \T_ELSE || $this->tokens[$i]['code'] === \T_ELSEIF)) {
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            StatusWriter::write('continuing');
                        }
                        return $i - 1;
                    } else {
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            StatusWriter::write('backtracking');
                        }
                        return $stackPtr;
                    }
                }
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    StatusWriter::write('* token is an opening condition *', $depth);
                }
                $isShared = $this->scopeOpeners[$tokenType]['shared'] === \true;
                if (isset($this->tokens[$i]['scope_condition']) === \true) {
                    // We've been here before.
                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        StatusWriter::write('* already processed, skipping *', $depth);
                    }
                    if ($isShared === \false && isset($this->tokens[$i]['scope_closer']) === \true) {
                        $i = $this->tokens[$i]['scope_closer'];
                    }
                    continue;
                } elseif ($currType === $tokenType && $isShared === \false && $opener === null) {
                    // We haven't yet found our opener, but we have found another
                    // scope opener which is the same type as us, and we don't
                    // share openers, so we will never find one.
                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        StatusWriter::write('* it was another token\'s opener, bailing *', $depth);
                    }
                    return $stackPtr;
                } else {
                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        StatusWriter::write('* searching for opener *', $depth);
                    }
                    if (isset($this->scopeOpeners[$tokenType]['end'][\T_CLOSE_CURLY_BRACKET]) === \true) {
                        $oldIgnore = $ignore;
                        $ignore = 0;
                    }
                    // PHP has a max nesting level for functions. Stop before we hit that limit
                    // because too many loops means we've run into trouble anyway.
                    if ($depth > 50) {
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            StatusWriter::write('* reached maximum nesting level; aborting *', $depth);
                        }
                        throw new TokenizerException('Maximum nesting level reached; file could not be processed');
                    }
                    $oldDepth = $depth;
                    if ($isShared === \true && isset($this->scopeOpeners[$tokenType]['with'][$currType]) === \true) {
                        // Don't allow the depth to increment because this is
                        // possibly not a true nesting if we are sharing our closer.
                        // This can happen, for example, when a SWITCH has a large
                        // number of CASE statements with the same shared BREAK.
                        $depth--;
                    }
                    $i = self::recurseScopeMap($i, $depth + 1, $ignore);
                    $depth = $oldDepth;
                    if (isset($this->scopeOpeners[$tokenType]['end'][\T_CLOSE_CURLY_BRACKET]) === \true) {
                        $ignore = $oldIgnore;
                    }
                }
            }
            if (isset($this->scopeOpeners[$currType]['start'][$tokenType]) === \true && $opener === null) {
                if ($tokenType === \T_OPEN_CURLY_BRACKET) {
                    if (isset($this->tokens[$stackPtr]['parenthesis_closer']) === \true && $i < $this->tokens[$stackPtr]['parenthesis_closer']) {
                        // We found a curly brace inside the condition of the
                        // current scope opener, so it must be a string offset.
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            StatusWriter::write('* ignoring curly brace inside condition *', $depth);
                        }
                        $ignore++;
                    } else {
                        // Make sure this is actually an opener and not a
                        // string offset (e.g., $var{0}).
                        for ($x = $i - 1; $x > 0; $x--) {
                            if (isset(Tokens::EMPTY_TOKENS[$this->tokens[$x]['code']]) === \true) {
                                continue;
                            } else {
                                // If the first non-whitespace/comment token looks like this
                                // brace is a string offset, or this brace is mid-way through
                                // a new statement, it isn't a scope opener.
                                $disallowed = Tokens::ASSIGNMENT_TOKENS;
                                $disallowed += [\T_DOLLAR => \true, \T_VARIABLE => \true, \T_OBJECT_OPERATOR => \true, \T_NULLSAFE_OBJECT_OPERATOR => \true, \T_COMMA => \true, \T_OPEN_PARENTHESIS => \true];
                                if (isset($disallowed[$this->tokens[$x]['code']]) === \true) {
                                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                                        StatusWriter::write('* ignoring curly brace *', $depth);
                                    }
                                    $ignore++;
                                }
                                break;
                            }
                        }
                    }
                }
                if ($ignore === 0 || $tokenType !== \T_OPEN_CURLY_BRACKET) {
                    $openerNested = isset($this->tokens[$i]['nested_parenthesis']);
                    $ownerNested = isset($this->tokens[$stackPtr]['nested_parenthesis']);
                    if ($openerNested === \true && $ownerNested === \false || $openerNested === \false && $ownerNested === \true || $openerNested === \true && $this->tokens[$i]['nested_parenthesis'] !== $this->tokens[$stackPtr]['nested_parenthesis']) {
                        // We found the a token that looks like the opener, but it's nested differently.
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            $type = $this->tokens[$i]['type'];
                            StatusWriter::write("* ignoring possible opener {$i}:{$type} as nested parenthesis don't match *", $depth);
                        }
                    } else {
                        // We found the opening scope token for $currType.
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            $type = $this->tokens[$stackPtr]['type'];
                            StatusWriter::write("=> Found scope opener for {$stackPtr}:{$type}", $depth);
                        }
                        $opener = $i;
                    }
                }
            } elseif ($tokenType === \T_SEMICOLON && $opener === null && (isset($this->tokens[$stackPtr]['parenthesis_closer']) === \false || $i > $this->tokens[$stackPtr]['parenthesis_closer'])) {
                // Found the end of a statement but still haven't
                // found our opener, so we are never going to find one.
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    $type = $this->tokens[$stackPtr]['type'];
                    StatusWriter::write("=> Found end of statement before scope opener for {$stackPtr}:{$type}, continuing", $depth);
                }
                return $i - 1;
            } elseif ($tokenType === \T_OPEN_PARENTHESIS) {
                if (isset($this->tokens[$i]['parenthesis_owner']) === \true) {
                    $owner = $this->tokens[$i]['parenthesis_owner'];
                    if (isset(Tokens::SCOPE_OPENERS[$this->tokens[$owner]['code']]) === \true && isset($this->tokens[$i]['parenthesis_closer']) === \true) {
                        // If we get into here, then we opened a parenthesis for
                        // a scope (eg. an if or else if) so we need to update the
                        // start of the line so that when we check to see
                        // if the closing parenthesis is more than n lines away from
                        // the statement, we check from the closing parenthesis.
                        $startLine = $this->tokens[$this->tokens[$i]['parenthesis_closer']]['line'];
                    }
                }
            } elseif ($tokenType === \T_OPEN_CURLY_BRACKET && $opener !== null) {
                // We opened something that we don't have a scope opener for.
                // Examples of this are curly brackets for string offsets etc.
                // We want to ignore this so that we don't have an invalid scope
                // map.
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    StatusWriter::write('* ignoring curly brace *', $depth);
                }
                $ignore++;
            } elseif ($tokenType === \T_CLOSE_CURLY_BRACKET && $ignore > 0) {
                // We found the end token for the opener we were ignoring.
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    StatusWriter::write('* finished ignoring curly brace *', $depth);
                }
                $ignore--;
            } elseif ($opener === null && isset($this->scopeOpeners[$currType]) === \true) {
                // If we still haven't found the opener after 30 lines,
                // we're not going to find it, unless we know it requires
                // an opener (in which case we better keep looking) or the last
                // token was empty (in which case we'll just confirm there is
                // more code in this file and not just a big comment).
                if ($this->tokens[$i]['line'] >= $startLine + 30 && isset(Tokens::EMPTY_TOKENS[$this->tokens[$i - 1]['code']]) === \false) {
                    if ($this->scopeOpeners[$currType]['strict'] === \true) {
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            $type = $this->tokens[$stackPtr]['type'];
                            $lines = $this->tokens[$i]['line'] - $startLine;
                            StatusWriter::write("=> Still looking for {$stackPtr}:{$type} scope opener after {$lines} lines", $depth);
                        }
                    } else {
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            $type = $this->tokens[$stackPtr]['type'];
                            StatusWriter::write("=> Couldn't find scope opener for {$stackPtr}:{$type}, bailing", $depth);
                        }
                        return $stackPtr;
                    }
                }
            } elseif ($opener !== null && $tokenType !== \T_BREAK && isset($this->endScopeTokens[$tokenType]) === \true) {
                if (isset($this->tokens[$i]['scope_condition']) === \false) {
                    if ($ignore > 0) {
                        // We found the end token for the opener we were ignoring.
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            StatusWriter::write('* finished ignoring curly brace *', $depth);
                        }
                        $ignore--;
                    } else {
                        // We found a token that closes the scope but it doesn't
                        // have a condition, so it belongs to another token and
                        // our token doesn't have a closer, so pretend this is
                        // the closer.
                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                            $type = $this->tokens[$stackPtr]['type'];
                            StatusWriter::write("=> Found (unexpected) scope closer for {$stackPtr}:{$type}", $depth);
                        }
                        foreach ([$stackPtr, $opener] as $token) {
                            $this->tokens[$token]['scope_condition'] = $stackPtr;
                            $this->tokens[$token]['scope_opener'] = $opener;
                            $this->tokens[$token]['scope_closer'] = $i;
                        }
                        return $i - 1;
                    }
                }
            }
        }
        return $stackPtr;
    }
    /**
     * Constructs the level map.
     *
     * The level map adds a 'level' index to each token which indicates the
     * depth that a token within a set of scope blocks. It also adds a
     * 'conditions' index which is an array of the scope conditions that opened
     * each of the scopes - position 0 being the first scope opener.
     *
     * @return void
     */
    private function createLevelMap()
    {
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            StatusWriter::write('*** START LEVEL MAP ***', 1);
        }
        $this->numTokens = \count($this->tokens);
        $level = 0;
        $conditions = [];
        $lastOpener = null;
        $openers = [];
        for ($i = 0; $i < $this->numTokens; $i++) {
            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                $type = $this->tokens[$i]['type'];
                $line = $this->tokens[$i]['line'];
                $len = $this->tokens[$i]['length'];
                $col = $this->tokens[$i]['column'];
                $content = Common::prepareForOutput($this->tokens[$i]['content']);
                $statusMessage = "Process token {$i} on line {$line} [col:{$col};len:{$len};lvl:{$level};";
                if (empty($conditions) !== \true) {
                    $conditionString = 'conds;';
                    foreach ($conditions as $condition) {
                        $conditionString .= Tokens::tokenName($condition) . ',';
                    }
                    $statusMessage .= \rtrim($conditionString, ',') . ';';
                }
                $statusMessage .= "]: {$type} => {$content}";
                StatusWriter::write($statusMessage, $level + 1);
            }
            $this->tokens[$i]['level'] = $level;
            $this->tokens[$i]['conditions'] = $conditions;
            if (isset($this->tokens[$i]['scope_condition']) === \true) {
                // Check to see if this token opened the scope.
                if ($this->tokens[$i]['scope_opener'] === $i) {
                    $stackPtr = $this->tokens[$i]['scope_condition'];
                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        $type = $this->tokens[$stackPtr]['type'];
                        StatusWriter::write("=> Found scope opener for {$stackPtr}:{$type}", $level + 1);
                    }
                    $stackPtr = $this->tokens[$i]['scope_condition'];
                    // If we find a scope opener that has a shared closer,
                    // then we need to go back over the condition map that we
                    // just created and fix ourselves as we just added some
                    // conditions where there was none. This happens for T_CASE
                    // statements that are using the same break statement.
                    if ($lastOpener !== null && $this->tokens[$lastOpener]['scope_closer'] === $this->tokens[$i]['scope_closer']) {
                        // This opener shares its closer with the previous opener,
                        // but we still need to check if the two openers share their
                        // closer with each other directly (like CASE and DEFAULT)
                        // or if they are just sharing because one doesn't have a
                        // closer (like CASE with no BREAK using a SWITCHes closer).
                        $thisType = $this->tokens[$this->tokens[$i]['scope_condition']]['code'];
                        $opener = $this->tokens[$lastOpener]['scope_condition'];
                        $isShared = isset($this->scopeOpeners[$thisType]['with'][$this->tokens[$opener]['code']]);
                        \reset($this->scopeOpeners[$thisType]['end']);
                        \reset($this->scopeOpeners[$this->tokens[$opener]['code']]['end']);
                        $sameEnd = \current($this->scopeOpeners[$thisType]['end']) === \current($this->scopeOpeners[$this->tokens[$opener]['code']]['end']);
                        if ($isShared === \true && $sameEnd === \true) {
                            $badToken = $opener;
                            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                                $type = $this->tokens[$badToken]['type'];
                                StatusWriter::write("* shared closer, cleaning up {$badToken}:{$type} *", $level + 1);
                            }
                            for ($x = $this->tokens[$i]['scope_condition']; $x <= $i; $x++) {
                                $oldConditions = $this->tokens[$x]['conditions'];
                                $oldLevel = $this->tokens[$x]['level'];
                                $this->tokens[$x]['level']--;
                                unset($this->tokens[$x]['conditions'][$badToken]);
                                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                                    $type = $this->tokens[$x]['type'];
                                    $oldConds = '';
                                    foreach ($oldConditions as $condition) {
                                        $oldConds .= Tokens::tokenName($condition) . ',';
                                    }
                                    $oldConds = \rtrim($oldConds, ',');
                                    $newConds = '';
                                    foreach ($this->tokens[$x]['conditions'] as $condition) {
                                        $newConds .= Tokens::tokenName($condition) . ',';
                                    }
                                    $newConds = \rtrim($newConds, ',');
                                    $newLevel = $this->tokens[$x]['level'];
                                    StatusWriter::write("* cleaned {$x}:{$type} *", $level + 1);
                                    StatusWriter::write("=> level changed from {$oldLevel} to {$newLevel}", $level + 2);
                                    StatusWriter::write("=> conditions changed from {$oldConds} to {$newConds}", $level + 2);
                                }
                            }
                            unset($conditions[$badToken]);
                            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                                $type = $this->tokens[$badToken]['type'];
                                StatusWriter::write("* token {$badToken}:{$type} removed from conditions array *", $level + 1);
                            }
                            unset($openers[$lastOpener]);
                            $level--;
                            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                                StatusWriter::write('* level decreased *', $level + 2);
                            }
                        }
                    }
                    $level++;
                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        StatusWriter::write('* level increased *', $level + 1);
                    }
                    $conditions[$stackPtr] = $this->tokens[$stackPtr]['code'];
                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                        $type = $this->tokens[$stackPtr]['type'];
                        StatusWriter::write("* token {$stackPtr}:{$type} added to conditions array *", $level + 1);
                    }
                    $lastOpener = $this->tokens[$i]['scope_opener'];
                    if ($lastOpener !== null) {
                        $openers[$lastOpener] = $lastOpener;
                    }
                } elseif ($lastOpener !== null && $this->tokens[$lastOpener]['scope_closer'] === $i) {
                    foreach (\array_reverse($openers) as $opener) {
                        if ($this->tokens[$opener]['scope_closer'] === $i) {
                            $oldOpener = \array_pop($openers);
                            if (empty($openers) === \false) {
                                $lastOpener = \array_pop($openers);
                                $openers[$lastOpener] = $lastOpener;
                            } else {
                                $lastOpener = null;
                            }
                            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                                $type = $this->tokens[$oldOpener]['type'];
                                StatusWriter::write("=> Found scope closer for {$oldOpener}:{$type}", $level + 1);
                            }
                            $oldCondition = \array_pop($conditions);
                            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                                StatusWriter::write('* token ' . Tokens::tokenName($oldCondition) . ' removed from conditions array *', $level + 1);
                            }
                            // Make sure this closer actually belongs to us.
                            // Either the condition also has to think this is the
                            // closer, or it has to allow sharing with us.
                            $condition = $this->tokens[$this->tokens[$i]['scope_condition']]['code'];
                            if ($condition !== $oldCondition) {
                                if (isset($this->scopeOpeners[$oldCondition]['with'][$condition]) === \false) {
                                    $badToken = $this->tokens[$oldOpener]['scope_condition'];
                                    if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                                        $type = Tokens::tokenName($oldCondition);
                                        StatusWriter::write("* scope closer was bad, cleaning up {$badToken}:{$type} *", $level + 1);
                                    }
                                    for ($x = $oldOpener + 1; $x <= $i; $x++) {
                                        $oldConditions = $this->tokens[$x]['conditions'];
                                        $oldLevel = $this->tokens[$x]['level'];
                                        $this->tokens[$x]['level']--;
                                        unset($this->tokens[$x]['conditions'][$badToken]);
                                        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                                            $type = $this->tokens[$x]['type'];
                                            $oldConds = '';
                                            foreach ($oldConditions as $condition) {
                                                $oldConds .= Tokens::tokenName($condition) . ',';
                                            }
                                            $oldConds = \rtrim($oldConds, ',');
                                            $newConds = '';
                                            foreach ($this->tokens[$x]['conditions'] as $condition) {
                                                $newConds .= Tokens::tokenName($condition) . ',';
                                            }
                                            $newConds = \rtrim($newConds, ',');
                                            $newLevel = $this->tokens[$x]['level'];
                                            StatusWriter::write("* cleaned {$x}:{$type} *", $level + 1);
                                            StatusWriter::write("=> level changed from {$oldLevel} to {$newLevel}", $level + 2);
                                            StatusWriter::write("=> conditions changed from {$oldConds} to {$newConds}", $level + 2);
                                        }
                                    }
                                }
                            }
                            $level--;
                            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                                StatusWriter::write('* level decreased *', $level + 2);
                            }
                            $this->tokens[$i]['level'] = $level;
                            $this->tokens[$i]['conditions'] = $conditions;
                        }
                    }
                }
            }
        }
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            StatusWriter::write('*** END LEVEL MAP ***', 1);
        }
    }
}
