<?php

/**
 * Tokenizes doc block comments.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Tokenizers;

use PHP_CodeSniffer\Util\Common;
use PHP_CodeSniffer\Util\Writers\StatusWriter;
class Comment
{
    /**
     * Splits a single doc block comment token up into something that can be easily iterated over.
     *
     * @param string $comment  The doc block comment string to parse.
     * @param string $eolChar  The EOL character to use for splitting strings.
     * @param int    $stackPtr The position of the token in the "new"/final token stream.
     *
     * @return array<int, array<string, string|int|array<int>>>
     */
    public function tokenizeString(string $comment, string $eolChar, int $stackPtr)
    {
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            StatusWriter::write('*** START COMMENT TOKENIZING ***', 2);
        }
        $tokens = [];
        $numChars = strlen($comment);
        /*
            Doc block comments start with /*, but typically contain an
            extra star when they are used for function and class comments.
        */
        $char = $numChars - strlen(ltrim($comment, '/*'));
        $lastChars = substr($comment, -2);
        if ($char === $numChars && $lastChars === '*/') {
            // Edge case: docblock without whitespace or contents.
            $openTag = (string) substr($comment, 0, -2);
            $comment = $lastChars;
        } else {
            $openTag = (string) substr($comment, 0, $char);
            $comment = ltrim($comment, '/*');
        }
        $tokens[$stackPtr] = ['content' => $openTag, 'code' => \T_DOC_COMMENT_OPEN_TAG, 'type' => 'T_DOC_COMMENT_OPEN_TAG', 'comment_opener' => $stackPtr, 'comment_tags' => []];
        $openPtr = $stackPtr;
        $stackPtr++;
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            $content = Common::prepareForOutput($openTag);
            StatusWriter::write("Create comment token: T_DOC_COMMENT_OPEN_TAG => {$content}", 2);
        }
        /*
            Strip off the close tag so it doesn't interfere with any
            of our comment line processing. The token will be added to the
            stack just before we return it.
        */
        $closeTag = ['content' => (string) substr($comment, strlen(rtrim($comment, '/*'))), 'code' => \T_DOC_COMMENT_CLOSE_TAG, 'type' => 'T_DOC_COMMENT_CLOSE_TAG', 'comment_opener' => $openPtr];
        if ($closeTag['content'] === \false) {
            // In PHP < 8.0 substr() can return `false` instead of always returning a string.
            $closeTag['content'] = '';
        }
        $string = rtrim($comment, '/*');
        /*
            Process each line of the comment.
        */
        $lines = explode($eolChar, $string);
        $numLines = count($lines);
        foreach ($lines as $lineNum => $string) {
            if ($lineNum !== $numLines - 1) {
                $string .= $eolChar;
            }
            $char = 0;
            $numChars = strlen($string);
            // We've started a new line, so process the indent.
            $space = $this->collectWhitespace($string, $char, $numChars);
            if ($space !== null) {
                $tokens[$stackPtr] = $space;
                $tokens[$stackPtr]['comment_opener'] = $openPtr;
                $stackPtr++;
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    $content = Common::prepareForOutput($space['content']);
                    StatusWriter::write("Create comment token: T_DOC_COMMENT_WHITESPACE => {$content}", 2);
                }
                $char += strlen($space['content']);
                if ($char === $numChars) {
                    break;
                }
            }
            if ($string === '') {
                continue;
            }
            if ($lineNum > 0 && $string[$char] === '*') {
                // This is a function or class doc block line.
                $char++;
                $tokens[$stackPtr] = ['content' => '*', 'code' => \T_DOC_COMMENT_STAR, 'type' => 'T_DOC_COMMENT_STAR', 'comment_opener' => $openPtr];
                $stackPtr++;
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    StatusWriter::write('Create comment token: T_DOC_COMMENT_STAR => *', 2);
                }
            }
            // Now we are ready to process the actual content of the line.
            $lineTokens = $this->processLine($string, $eolChar, $char, $numChars);
            foreach ($lineTokens as $lineToken) {
                $tokens[$stackPtr] = $lineToken;
                $tokens[$stackPtr]['comment_opener'] = $openPtr;
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    $content = Common::prepareForOutput($lineToken['content']);
                    $type = $lineToken['type'];
                    StatusWriter::write("Create comment token: {$type} => {$content}", 2);
                }
                if ($lineToken['code'] === \T_DOC_COMMENT_TAG) {
                    $tokens[$openPtr]['comment_tags'][] = $stackPtr;
                }
                $stackPtr++;
            }
        }
        $tokens[$stackPtr] = $closeTag;
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            $content = Common::prepareForOutput($closeTag['content']);
            StatusWriter::write("Create comment token: T_DOC_COMMENT_CLOSE_TAG => {$content}", 2);
        }
        // Only now do we know the stack pointer to the docblock closer,
        // so add it to all previously created comment tokens.
        foreach ($tokens as $ptr => $token) {
            $tokens[$ptr]['comment_closer'] = $stackPtr;
        }
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            StatusWriter::write('*** END COMMENT TOKENIZING ***', 2);
        }
        return $tokens;
    }
    /**
     * Process a single line of a comment.
     *
     * @param string $comment The comment string being tokenized.
     * @param string $eolChar The EOL character to use for splitting strings.
     * @param int    $start   The position in the string to start processing.
     * @param int    $end     The position in the string to end processing.
     *
     * @return array<int, array<string, string|int>>
     */
    private function processLine(string $comment, string $eolChar, int $start, int $end)
    {
        $tokens = [];
        // Collect content padding.
        $space = $this->collectWhitespace($comment, $start, $end);
        if ($space !== null) {
            $tokens[] = $space;
            $start += strlen($space['content']);
        }
        if (isset($comment[$start]) === \false) {
            return $tokens;
        }
        if ($comment[$start] === '@') {
            // The content up until the first whitespace is the tag name.
            $matches = [];
            preg_match('/@[^\s]+/', $comment, $matches, 0, $start);
            if (isset($matches[0]) === \true && substr(strtolower($matches[0]), 0, 7) !== '@phpcs:') {
                $tagName = $matches[0];
                $start += strlen($tagName);
                $tokens[] = ['content' => $tagName, 'code' => \T_DOC_COMMENT_TAG, 'type' => 'T_DOC_COMMENT_TAG'];
                // Then there will be some whitespace.
                $space = $this->collectWhitespace($comment, $start, $end);
                if ($space !== null) {
                    $tokens[] = $space;
                    $start += strlen($space['content']);
                }
            }
        }
        // Process the rest of the line.
        $eol = strpos($comment, $eolChar, $start);
        if ($eol === \false) {
            $eol = $end;
        }
        if ($eol > $start) {
            $tokens[] = ['content' => (string) substr($comment, $start, $eol - $start), 'code' => \T_DOC_COMMENT_STRING, 'type' => 'T_DOC_COMMENT_STRING'];
        }
        if ($eol !== $end) {
            $tokens[] = ['content' => (string) substr($comment, $eol, strlen($eolChar)), 'code' => \T_DOC_COMMENT_WHITESPACE, 'type' => 'T_DOC_COMMENT_WHITESPACE'];
        }
        return $tokens;
    }
    /**
     * Collect consecutive whitespace into a single token.
     *
     * @param string $comment The comment string being tokenized.
     * @param int    $start   The position in the string to start processing.
     * @param int    $end     The position in the string to end processing.
     *
     * @return array<string, string|int>|null
     */
    private function collectWhitespace(string $comment, int $start, int $end)
    {
        $space = '';
        for ($start; $start < $end; $start++) {
            if ($comment[$start] !== ' ' && $comment[$start] !== "\t") {
                break;
            }
            $space .= $comment[$start];
        }
        if ($space === '') {
            return null;
        }
        return ['content' => $space, 'code' => \T_DOC_COMMENT_WHITESPACE, 'type' => 'T_DOC_COMMENT_WHITESPACE'];
    }
}
