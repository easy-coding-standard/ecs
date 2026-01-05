<?php

/**
 * A doc generator that outputs text-based documentation.
 *
 * Output is designed to be displayed in a terminal and is wrapped to 100 characters.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Juliette Reinders Folmer <phpcs_nospam@adviesenzo.nl>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Generators;

use DOMElement;
class Text extends \PHP_CodeSniffer\Generators\Generator
{
    /**
     * Process the documentation for a single sniff.
     *
     * @param \DOMElement $doc The DOMElement object for the sniff.
     *                         It represents the "documentation" tag in the XML
     *                         standard file.
     *
     * @return void
     */
    public function processSniff(DOMElement $doc)
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
            echo $this->getFormattedTitle($doc), $content;
        }
    }
    /**
     * Format the title area for a single sniff.
     *
     * @param \DOMElement $doc The DOMElement object for the sniff.
     *                         It represents the "documentation" tag in the XML
     *                         standard file.
     *
     * @since 3.12.0 Replaces the Text::printTitle() method,
     *               which was deprecated in 3.12.0 and removed in 4.0.0.
     *
     * @return string
     */
    protected function getFormattedTitle(DOMElement $doc)
    {
        $title = $this->getTitle($doc);
        $standard = $this->ruleset->name;
        $displayTitle = "{$standard} CODING STANDARD: {$title}";
        $titleLength = \strlen($displayTitle);
        $output = \PHP_EOL;
        $output .= \str_repeat('-', $titleLength + 4);
        $output .= \strtoupper(\PHP_EOL . "| {$displayTitle} |" . \PHP_EOL);
        $output .= \str_repeat('-', $titleLength + 4);
        $output .= \PHP_EOL . \PHP_EOL;
        return $output;
    }
    /**
     * Format a text block found in a standard.
     *
     * @param \DOMElement $node The DOMElement object for the text block.
     *
     * @since 3.12.0 Replaces the Text::printTextBlock() method,
     *               which was deprecated in 3.12.0 and removed in 4.0.0.
     *
     * @return string
     */
    protected function getFormattedTextBlock(DOMElement $node)
    {
        $text = $node->nodeValue;
        if (empty($text) === \true) {
            return '';
        }
        $text = \trim($text);
        $text = \str_replace(['<em>', '</em>'], '*', $text);
        $nodeLines = \explode("\n", $text);
        $nodeLines = \array_map('trim', $nodeLines);
        $text = \implode(\PHP_EOL, $nodeLines);
        return \wordwrap($text, 100, \PHP_EOL) . \PHP_EOL . \PHP_EOL;
    }
    /**
     * Format a code comparison block found in a standard.
     *
     * @param \DOMElement $node The DOMElement object for the code comparison block.
     *
     * @since 3.12.0 Replaces the Text::printCodeComparisonBlock() method,
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
        $firstTitleLines = $this->codeTitleToLines($firstCodeElm);
        $firstLines = $this->codeToLines($firstCodeElm);
        $secondTitleLines = $this->codeTitleToLines($secondCodeElm);
        $secondLines = $this->codeToLines($secondCodeElm);
        $titleRow = '';
        if ($firstTitleLines !== [] || $secondTitleLines !== []) {
            $titleRow = $this->linesToTableRows($firstTitleLines, $secondTitleLines);
            $titleRow .= \str_repeat('-', 100) . \PHP_EOL;
        }
        $codeRow = '';
        if ($firstLines !== [] || $secondLines !== []) {
            $codeRow = $this->linesToTableRows($firstLines, $secondLines);
            $codeRow .= \str_repeat('-', 100) . \PHP_EOL . \PHP_EOL;
        }
        $output = '';
        if ($titleRow !== '' || $codeRow !== '') {
            $output = \str_repeat('-', 41);
            $output .= ' CODE COMPARISON ';
            $output .= \str_repeat('-', 42) . \PHP_EOL;
            $output .= $titleRow;
            $output .= $codeRow;
        }
        return $output;
    }
    /**
     * Retrieve a code block title and split it into lines for use in an ASCII table.
     *
     * @param \DOMElement $codeElm The DOMElement object for a code block.
     *
     * @since 3.12.0
     *
     * @return array<string>
     */
    private function codeTitleToLines(DOMElement $codeElm)
    {
        $title = \trim($codeElm->getAttribute('title'));
        if ($title === '') {
            return [];
        }
        $title = \wordwrap($title, 46, "\n");
        return \explode("\n", $title);
    }
    /**
     * Retrieve a code block contents and split it into lines for use in an ASCII table.
     *
     * @param \DOMElement $codeElm The DOMElement object for a code block.
     *
     * @since 3.12.0
     *
     * @return array<string>
     */
    private function codeToLines(DOMElement $codeElm)
    {
        $code = \trim($codeElm->nodeValue);
        if ($code === '') {
            return [];
        }
        $code = \str_replace(['<em>', '</em>'], '', $code);
        return \explode("\n", $code);
    }
    /**
     * Transform two sets of text lines into rows for use in an ASCII table.
     *
     * The sets may not contains an equal amount of lines, while the resulting rows should.
     *
     * @param array<string> $column1Lines Lines of text to place in column 1.
     * @param array<string> $column2Lines Lines of text to place in column 2.
     *
     * @return string
     */
    private function linesToTableRows(array $column1Lines, array $column2Lines)
    {
        $maxLines = \max(\count($column1Lines), \count($column2Lines));
        $rows = '';
        for ($i = 0; $i < $maxLines; $i++) {
            $column1Text = $column1Lines[$i] ?? '';
            $column2Text = $column2Lines[$i] ?? '';
            $rows .= '| ';
            $rows .= $column1Text . \str_repeat(' ', \max(0, 47 - \strlen($column1Text)));
            $rows .= '| ';
            $rows .= $column2Text . \str_repeat(' ', \max(0, 48 - \strlen($column2Text)));
            $rows .= '|' . \PHP_EOL;
        }
        return $rows;
    }
}
