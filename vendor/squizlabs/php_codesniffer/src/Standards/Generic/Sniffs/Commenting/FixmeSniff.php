<?php

/**
 * Warns about FIXME comments.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Sam Graham <php-codesniffer@illusori.co.uk>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\Generic\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
class FixmeSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int|string>
     */
    public function register()
    {
        return \array_diff(Tokens::COMMENT_TOKENS, Tokens::PHPCS_ANNOTATION_TOKENS);
    }
    /**
     * Processes this sniff, when one of its tokens is encountered.
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
        $content = $tokens[$stackPtr]['content'];
        $matches = [];
        \preg_match('/(?:\\A|[^\\p{L}]+)fixme([^\\p{L}]+(.*)|\\Z)/ui', $content, $matches);
        if (empty($matches) === \false) {
            // Clear whitespace and some common characters not required at
            // the end of a fixme message to make the error more informative.
            $type = 'CommentFound';
            $fixmeMessage = \trim($matches[1]);
            $fixmeMessage = \trim($fixmeMessage, '-:[](). ');
            $error = 'Comment refers to a FIXME task';
            $data = [$fixmeMessage];
            if ($fixmeMessage !== '') {
                $type = 'TaskFound';
                $error .= ' "%s"';
            }
            $phpcsFile->addError($error, $stackPtr, $type, $data);
        }
    }
}
