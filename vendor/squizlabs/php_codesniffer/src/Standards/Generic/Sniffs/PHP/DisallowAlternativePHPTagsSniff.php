<?php

/**
 * Verifies that no alternative PHP tags are used.
 *
 * If alternative PHP open tags are found, this sniff can fix both the open and close tags.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\Generic\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
class DisallowAlternativePHPTagsSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int|string>
     */
    public function register()
    {
        return [\T_INLINE_HTML];
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
        $openTag = $tokens[$stackPtr];
        $content = $openTag['content'];
        if (\trim($content) === '') {
            return;
        }
        // Account for script open tags.
        if (\preg_match('`(<script (?:[^>]+)?language=[\'"]?php[\'"]?(?:[^>]+)?>)`i', $content, $match) === 1) {
            $error = 'Script style opening tag used; expected "<?php" but found "%s"';
            $snippet = $this->getSnippet($content, $match[1]);
            $data = [$match[1] . $snippet];
            $phpcsFile->addError($error, $stackPtr, 'ScriptOpenTagFound', $data);
            return;
        }
        // Account for ASP style tags.
        if (\strpos($content, '<%=') !== \false) {
            $error = 'Possible use of ASP style short opening tags detected; found: %s';
            $snippet = $this->getSnippet($content, '<%=');
            $data = ['<%=' . $snippet];
            $phpcsFile->addWarning($error, $stackPtr, 'MaybeASPShortOpenTagFound', $data);
        } elseif (\strpos($content, '<%') !== \false) {
            $error = 'Possible use of ASP style opening tags detected; found: %s';
            $snippet = $this->getSnippet($content, '<%');
            $data = ['<%' . $snippet];
            $phpcsFile->addWarning($error, $stackPtr, 'MaybeASPOpenTagFound', $data);
        }
    }
    /**
     * Get a snippet from a HTML token.
     *
     * @param string $content The content of the HTML token.
     * @param string $start   Partial string to use as a starting point for the snippet.
     * @param int    $length  The target length of the snippet to get. Defaults to 40.
     *
     * @return string
     */
    protected function getSnippet(string $content, string $start = '', int $length = 40)
    {
        $startPos = 0;
        if ($start !== '') {
            $startPos = \strpos($content, $start);
            if ($startPos !== \false) {
                $startPos += \strlen($start);
            }
        }
        $snippet = (string) \substr($content, $startPos, $length);
        if (\strlen($content) - $startPos > $length) {
            $snippet .= '...';
        }
        return $snippet;
    }
}
