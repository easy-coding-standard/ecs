<?php

/**
 * A doc generator that outputs documentation in one big HTML file.
 *
 * Output is in one large HTML file and is designed for you to style with
 * your own stylesheet. It contains a table of contents at the top with anchors
 * to each sniff.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Juliette Reinders Folmer <phpcs_nospam@adviesenzo.nl>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Generators;

use DOMDocument;
use DOMElement;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Exceptions\GeneratorException;
class HTML extends \PHP_CodeSniffer\Generators\Generator
{
    /**
     * Stylesheet for the HTML output.
     *
     * @var string
     */
    private const STYLESHEET = '<style>
        body {
            background-color: #FFFFFF;
            font-size: 14px;
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
        }

        h1 {
            color: #666666;
            font-size: 20px;
            font-weight: bold;
            margin-top: 0px;
            background-color: #E6E7E8;
            padding: 20px;
            border: 1px solid #BBBBBB;
        }

        h2 {
            color: #00A5E3;
            font-size: 16px;
            font-weight: normal;
            margin-top: 50px;
        }

        h2 a.sniffanchor,
        h2 a.sniffanchor {
            color: #006C95;
            opacity: 0;
            padding: 0 3px;
            text-decoration: none;
            font-weight: bold;
        }
        h2:hover a.sniffanchor,
        h2:focus a.sniffanchor {
            opacity: 1;
        }

        .code-comparison {
            width: 100%;
        }

        .code-comparison td {
            border: 1px solid #CCCCCC;
        }

        .code-comparison-title, .code-comparison-code {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000000;
            vertical-align: top;
            padding: 4px;
            width: 50%;
            background-color: #F1F1F1;
            line-height: 15px;
        }

        .code-comparison-title {
            text-align: left;
            font-weight: 600;
        }

        .code-comparison-code {
            font-family: Courier;
            background-color: #F9F9F9;
        }

        .code-comparison-highlight {
            background-color: #DDF1F7;
            border: 1px solid #00A5E3;
            line-height: 15px;
        }

        .tag-line {
            text-align: center;
            width: 100%;
            margin-top: 30px;
            font-size: 12px;
        }

        .tag-line a {
            color: #000000;
        }
    </style>';
    /**
     * List of seen slugified anchors to ensure uniqueness.
     *
     * @var array<string, true>
     */
    private $seenAnchors = [];
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
        ob_start();
        try {
            parent::generate();
            $content = ob_get_clean();
        } catch (GeneratorException $e) {
            ob_end_clean();
            $content = '';
        }
        // If an exception was caught, rethrow it outside of the output buffer.
        if (isset($e) === \true) {
            throw $e;
        }
        // Clear anchor cache after Documentation generation.
        // The anchor generation for the TOC anchor links will use the same logic, so should end up with the same unique slugs.
        $this->seenAnchors = [];
        if (trim($content) !== '') {
            echo $this->getFormattedHeader();
            echo $this->getFormattedToc();
            echo $content;
            echo $this->getFormattedFooter();
        }
    }
    /**
     * Format the header of the HTML page.
     *
     * @since 3.12.0 Replaces the HTML::printHeader() method,
     *               which was deprecated in 3.12.0 and removed in 4.0.0.
     *
     * @return string
     */
    protected function getFormattedHeader()
    {
        $standard = $this->ruleset->name;
        $output = sprintf('<html>
 <head>
  <title>%1$s Coding Standards</title>
  %2$s
 </head>
 <body>
  <h1>%1$s Coding Standards</h1>', $standard, self::STYLESHEET);
        // Use the correct line endings based on the OS.
        return str_replace("\n", \PHP_EOL, $output) . \PHP_EOL;
    }
    /**
     * Format the table of contents for the standard.
     *
     * The TOC is just an unordered list of bookmarks to sniffs on the page.
     *
     * @since 3.12.0 Replaces the HTML::printToc() method,
     *               which was deprecated in 3.12.0 and removed in 4.0.0.
     *
     * @return string
     */
    protected function getFormattedToc()
    {
        // Only show a TOC when there are two or more docs to display.
        if (count($this->docFiles) < 2) {
            return '';
        }
        $output = '  <h2>Table of Contents</h2>' . \PHP_EOL;
        $output .= '  <ul class="toc">' . \PHP_EOL;
        $listItemTemplate = '   <li><a href="#%s">%s</a></li>' . \PHP_EOL;
        foreach ($this->docFiles as $file) {
            $doc = new DOMDocument();
            $doc->load($file);
            $documentation = $doc->getElementsByTagName('documentation')->item(0);
            $title = $this->getTitle($documentation);
            $output .= sprintf($listItemTemplate, $this->titleToAnchor($title), $title);
        }
        $output .= '  </ul>' . \PHP_EOL;
        return $output;
    }
    /**
     * Format the footer of the HTML page.
     *
     * @since 3.12.0 Replaces the HTML::printFooter() method,
     *               which was deprecated in 3.12.0 and removed in 4.0.0.
     *
     * @return string
     */
    protected function getFormattedFooter()
    {
        // Turn off errors so we don't get timezone warnings if people
        // don't have their timezone set.
        $errorLevel = error_reporting(0);
        $output = sprintf('  <div class="tag-line">Documentation generated on %s by <a href="https://github.com/PHPCSStandards/PHP_CodeSniffer">PHP_CodeSniffer %s</a></div>
 </body>
</html>', date('r'), Config::VERSION);
        error_reporting($errorLevel);
        // Use the correct line endings based on the OS.
        return str_replace("\n", \PHP_EOL, $output) . \PHP_EOL;
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
        if (trim($content) !== '') {
            $title = $this->getTitle($doc);
            printf('  <h2 id="%1$s">%2$s<a class="sniffanchor" href="#%1$s"> &sect; </a></h2>' . \PHP_EOL, $this->titleToAnchor($title), $title);
            echo $content;
        }
    }
    /**
     * Transform a title to a string which can be used as an HTML anchor.
     *
     * @param string $title The title.
     *
     * @since 3.12.0
     *
     * @return string
     */
    private function titleToAnchor(string $title)
    {
        // Slugify the text.
        $title = strtolower($title);
        $title = preg_replace('`[^a-z0-9\._-]`', '-', $title);
        if (isset($this->seenAnchors[$title]) === \true) {
            // Try to find a unique anchor for this title.
            for ($i = 2; isset($this->seenAnchors[$title . '-' . $i]) === \true; $i++) {
            }
            $title .= '-' . $i;
        }
        // Add to "seen" list.
        $this->seenAnchors[$title] = \true;
        return $title;
    }
    /**
     * Format a text block found in a standard.
     *
     * @param \DOMElement $node The DOMElement object for the text block.
     *
     * @since 3.12.0 Replaces the HTML::printTextBlock() method,
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
        $content = trim($content);
        $content = htmlspecialchars($content, \ENT_QUOTES | \ENT_SUBSTITUTE | \ENT_HTML401);
        // Allow only em tags.
        $content = str_replace('&lt;em&gt;', '<em>', $content);
        $content = str_replace('&lt;/em&gt;', '</em>', $content);
        $nodeLines = explode("\n", $content);
        $lineCount = count($nodeLines);
        $lines = [];
        for ($i = 0; $i < $lineCount; $i++) {
            $currentLine = trim($nodeLines[$i]);
            if (isset($nodeLines[$i + 1]) === \false) {
                // We're at the end of the text, just add the line.
                $lines[] = $currentLine;
            } else {
                $nextLine = trim($nodeLines[$i + 1]);
                if ($nextLine === '') {
                    // Next line is a blank line, end the paragraph and start a new one.
                    // Also skip over the blank line.
                    $lines[] = $currentLine . '</p>' . \PHP_EOL . '  <p class="text">';
                    ++$i;
                } else {
                    // Next line is not blank, so just add a line break.
                    $lines[] = $currentLine . '<br/>' . \PHP_EOL;
                }
            }
        }
        return '  <p class="text">' . implode('', $lines) . '</p>' . \PHP_EOL;
    }
    /**
     * Format a code comparison block found in a standard.
     *
     * @param \DOMElement $node The DOMElement object for the code comparison block.
     *
     * @since 3.12.0 Replaces the HTML::printCodeComparisonBlock() method,
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
            $titleRow .= "    <th class=\"code-comparison-title\">{$firstTitle}</th>" . \PHP_EOL;
            $titleRow .= "    <th class=\"code-comparison-title\">{$secondTitle}</th>" . \PHP_EOL;
            $titleRow .= '   </tr>' . \PHP_EOL;
        }
        $codeRow = '';
        if ($first !== '' || $second !== '') {
            $codeRow .= '   <tr>' . \PHP_EOL;
            $codeRow .= "    <td class=\"code-comparison-code\">{$first}</td>" . \PHP_EOL;
            $codeRow .= "    <td class=\"code-comparison-code\">{$second}</td>" . \PHP_EOL;
            $codeRow .= '   </tr>' . \PHP_EOL;
        }
        $output = '';
        if ($titleRow !== '' || $codeRow !== '') {
            $output = '  <table class="code-comparison">' . \PHP_EOL;
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
        $title = trim($codeElm->getAttribute('title'));
        return str_replace('  ', '&nbsp;&nbsp;', $title);
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
        $code = trim($code);
        $code = str_replace('<?php', '&lt;?php', $code);
        $code = str_replace(["\n", ' '], ['</br>', '&nbsp;'], $code);
        $code = str_replace(['<em>', '</em>'], ['<span class="code-comparison-highlight">', '</span>'], $code);
        return $code;
    }
}
