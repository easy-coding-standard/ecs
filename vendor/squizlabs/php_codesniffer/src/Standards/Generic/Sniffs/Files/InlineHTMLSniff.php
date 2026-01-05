<?php

/**
 * Ensures the whole file is PHP only, with no whitespace or inline HTML.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\Generic\Sniffs\Files;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
class InlineHTMLSniff implements Sniff
{
    /**
     * List of supported BOM definitions.
     *
     * Use encoding names as keys and hex BOM representations as values.
     *
     * @var array<string, string>
     */
    protected const BOM_DEFINITIONS = ['UTF-8' => 'efbbbf', 'UTF-16 (BE)' => 'feff', 'UTF-16 (LE)' => 'fffe'];
    /**
     * List of supported BOM definitions.
     *
     * @var array<string, string>
     *
     * @deprecated 4.0.0 Use the InlineHTMLSniff::BOM_DEFINITIONS constant instead.
     */
    protected $bomDefinitions = self::BOM_DEFINITIONS;
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
     * @param int                         $stackPtr  The position of the current token in
     *                                               the stack passed in $tokens.
     *
     * @return int|void
     */
    public function process(File $phpcsFile, int $stackPtr)
    {
        // Allow a byte-order mark.
        $tokens = $phpcsFile->getTokens();
        foreach (static::BOM_DEFINITIONS as $expectedBomHex) {
            $bomByteLength = \strlen($expectedBomHex) / 2;
            $htmlBomHex = \bin2hex((string) \substr($tokens[0]['content'], 0, $bomByteLength));
            if ($htmlBomHex === $expectedBomHex && \strlen($tokens[0]['content']) === $bomByteLength) {
                return;
            }
        }
        // Ignore shebang lines.
        $tokens = $phpcsFile->getTokens();
        if (\substr($tokens[$stackPtr]['content'], 0, 2) === '#!') {
            return;
        }
        $error = 'PHP files must only contain PHP code';
        $phpcsFile->addError($error, $stackPtr, 'Found');
        return $phpcsFile->numTokens;
    }
}
