<?php

/**
 * A doc generator that outputs documentation in Markdown format.
 *
 * @author    Stefano Kowalke <blueduck@gmx.net>
 * @author    Juliette Reinders Folmer <phpcs_nospam@adviesenzo.nl>
 * @copyright 2014 Arroba IT
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Generators;

use DOMElement;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Exceptions\GeneratorException;
class Markdown extends \PHP_CodeSniffer\Generators\Generator
{
    /**
     * Generates the documentation for a standard.
     *
     * @return void
     * @see    processSniff()
     *
     * @throws \PHP_CodeSniffer\Exceptions\GeneratorException If there is no <documentation> element
     *                                                        in the XML document.
     */
    public function generate()
    {
        if (empty($this->docFiles) === \true) {
            return;
        }
        \ob_start();
        try {
            parent::generate();
            $content = \ob_get_clean();
        } catch (GeneratorException $e) {
            \ob_end_clean();
            $content = '';
        }
        // If an exception was caught, rethrow it outside of the output buffer.
        if (isset($e) === \true) {
            throw $e;
        }
        if (\trim($content) !== '') {
            echo $this->getFormattedHeader();
            echo $content;
            echo $this->getFormattedFooter();
        }
    }
    /**
     * Format the markdown header.
     *
     * @since 3.12.0 Replaces the Markdown::printHeader() method,
     *               which was deprecated in 3.12.0 and removed in 4.0.0.
     *
     * @return string
     */
    protected function getFormattedHeader()
    {
        $standard = $this->ruleset->name;
        return "# {$standard} Coding Standard" . \PHP_EOL;
    }
    /**
     * Format the markdown footer.
     *
     * @since 3.12.0 Replaces the Markdown::printFooter() method,
     *               which was deprecated in 3.12.0 and removed in 4.0.0.
     *
     * @return string
     */
    protected function getFormattedFooter()
    {
        // Turn off errors so we don't get timezone warnings if people
        // don't have their timezone set.
        $errorLevel = \error_reporting(0);
        $output = \PHP_EOL . 'Documentation generated on ' . \date('r');
        $output .= ' by [PHP_CodeSniffer ' . Config::VERSION . '](https://github.com/PHPCSStandards/PHP_CodeSniffer)' . \PHP_EOL;
        \error_reporting($errorLevel);
        return $output;
    }
    /**
     * Process the documentation for a single sniff.
     *
     * @param \DOMElement $doc The DOMElement object for the sniff.
     *                         It represents the "documentation" tag in the XML
     *                         standard file.
     *
     * @return void
     */
    protected function processSniff(DOMElement $doc)
    {
        $content = '';
        foreach ($doc->childNodes as $node) {
            if ($node->nodeName === 'standard') {
                $content .= $this->getFormattedTextBlock($node);
            } elseif ($node->nodeName === 'code_comparison') {
                $content .= $this->getFormattedCodeComparisonBlock($node);
            }
        }
        if (\trim($content) !== '') {
            $title = $this->getTitle($doc);
            echo \PHP_EOL . "## {$title}" . \PHP_EOL . \PHP_EOL;
            echo $content;
        }
    }
    /**
     * Format a text block found in a standard.
     *
     * @param \DOMElement $node The DOMElement object for the text block.
     *
     * @since 3.12.0 Replaces the Markdown::printTextBlock() method,
     *               which was deprecated in 3.12.0 and removed in 4.0.0.
     *
     * @return string
     */
    protected function getFormattedTextBlock(DOMElement $node)
    {
        $content = $node->nodeValue;
        if (empty($content) === \true) {
            return '';
        }
        $content = \trim($content);
        $content = \htmlspecialchars($content, \ENT_QUOTES | \ENT_SUBSTITUTE | \ENT_HTML401);
        $content = \str_replace('&lt;em&gt;', '*', $content);
        $content = \str_replace('&lt;/em&gt;', '*', $content);
        $nodeLines = \explode("\n", $content);
        $lineCount = \count($nodeLines);
        $lines = [];
        for ($i = 0; $i < $lineCount; $i++) {
            $currentLine = \trim($nodeLines[$i]);
            if ($currentLine === '') {
                // The text contained a blank line. Respect this.
                $lines[] = '';
                continue;
            }
            // Check if the _next_ line is blank.
            if (isset($nodeLines[$i + 1]) === \false || \trim($nodeLines[$i + 1]) === '') {
                // Next line is blank, just add the line.
                $lines[] = $currentLine;
            } else {
                // Ensure that line breaks are respected in markdown.
                $lines[] = $currentLine . '  ';
            }
        }
        return \implode(\PHP_EOL, $lines) . \PHP_EOL;
    }
    /**
     * Format a code comparison block found in a standard.
     *
     * @param \DOMElement $node The DOMElement object for the code comparison block.
     *
     * @since 3.12.0 Replaces the Markdown::printCodeComparisonBlock() method,
     *               which was deprecated in 3.12.0 and removed in 4.0.0.
     *
     * @return string
     */
    protected function getFormattedCodeComparisonBlock(DOMElement $node)
    {
        $codeBlocks = $node->getElementsByTagName('code');
        $firstCodeElm = $codeBlocks->item(0);
        $secondCodeElm = $codeBlocks->item(1);
        if (isset($firstCodeElm, $secondCodeElm) === \false) {
            // Missing at least one code block.
            return '';
        }
        $firstTitle = $this->formatCodeTitle($firstCodeElm);
        $first = $this->formatCodeSample($firstCodeElm);
        $secondTitle = $this->formatCodeTitle($secondCodeElm);
        $second = $this->formatCodeSample($secondCodeElm);
        $titleRow = '';
        if ($firstTitle !== '' || $secondTitle !== '') {
            $titleRow .= '   <tr>' . \PHP_EOL;
            $titleRow .= "    <th>{$firstTitle}</th>" . \PHP_EOL;
            $titleRow .= "    <th>{$secondTitle}</th>" . \PHP_EOL;
            $titleRow .= '   </tr>' . \PHP_EOL;
        }
        $codeRow = '';
        if ($first !== '' || $second !== '') {
            $codeRow .= '   <tr>' . \PHP_EOL;
            $codeRow .= '<td>' . \PHP_EOL . \PHP_EOL;
            $codeRow .= "    {$first}" . \PHP_EOL . \PHP_EOL;
            $codeRow .= '</td>' . \PHP_EOL;
            $codeRow .= '<td>' . \PHP_EOL . \PHP_EOL;
            $codeRow .= "    {$second}" . \PHP_EOL . \PHP_EOL;
            $codeRow .= '</td>' . \PHP_EOL;
            $codeRow .= '   </tr>' . \PHP_EOL;
        }
        $output = '';
        if ($titleRow !== '' || $codeRow !== '') {
            $output .= '  <table>' . \PHP_EOL;
            $output .= $titleRow;
            $output .= $codeRow;
            $output .= '  </table>' . \PHP_EOL;
        }
        return $output;
    }
    /**
     * Retrieve a code block title and prepare it for output as HTML.
     *
     * @param \DOMElement $codeElm The DOMElement object for a code block.
     *
     * @since 3.12.0
     *
     * @return string
     */
    private function formatCodeTitle(DOMElement $codeElm)
    {
        $title = \trim($codeElm->getAttribute('title'));
        return \str_replace('  ', '&nbsp;&nbsp;', $title);
    }
    /**
     * Retrieve a code block contents and prepare it for output as HTML.
     *
     * @param \DOMElement $codeElm The DOMElement object for a code block.
     *
     * @since 3.12.0
     *
     * @return string
     */
    private function formatCodeSample(DOMElement $codeElm)
    {
        $code = (string) $codeElm->nodeValue;
        $code = \trim($code);
        $code = \str_replace("\n", \PHP_EOL . '    ', $code);
        $code = \str_replace(['<em>', '</em>'], '', $code);
        return $code;
    }
}
