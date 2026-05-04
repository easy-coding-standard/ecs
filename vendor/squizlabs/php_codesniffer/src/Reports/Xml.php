<?php

/**
 * XML report for PHP_CodeSniffer.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Reports;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\File;
use XMLWriter;
class Xml implements \PHP_CodeSniffer\Reports\Report
{
    /**
     * Generate a partial report for a single processed file.
     *
     * Function should return TRUE if it printed or stored data about the file
     * and FALSE if it ignored the file. Returning TRUE indicates that the file and
     * its data should be counted in the grand totals.
     *
     * @param array<string, string|int|array> $report      Prepared report data.
     *                                                     See the {@see Report} interface for a detailed specification.
     * @param \PHP_CodeSniffer\Files\File     $phpcsFile   The file being reported on.
     * @param bool                            $showSources Show sources?
     * @param int                             $width       Maximum allowed line width.
     *
     * @return bool
     */
    public function generateFileReport(array $report, File $phpcsFile, bool $showSources = \false, int $width = 80)
    {
        $out = new XMLWriter();
        $out->openMemory();
        $out->setIndent(\true);
        $out->setIndentString('    ');
        $out->startDocument('1.0', 'UTF-8');
        if ($report['errors'] === 0 && $report['warnings'] === 0) {
            // Nothing to print.
            return \false;
        }
        $out->startElement('file');
        $out->writeAttribute('name', $report['filename']);
        $out->writeAttribute('errors', $report['errors']);
        $out->writeAttribute('warnings', $report['warnings']);
        $out->writeAttribute('fixable', $report['fixable']);
        foreach ($report['messages'] as $line => $lineErrors) {
            foreach ($lineErrors as $column => $colErrors) {
                foreach ($colErrors as $error) {
                    $error['type'] = strtolower($error['type']);
                    if ($phpcsFile->config->encoding !== 'utf-8') {
                        $error['message'] = iconv($phpcsFile->config->encoding, 'utf-8', $error['message']);
                    }
                    $out->startElement($error['type']);
                    $out->writeAttribute('line', $line);
                    $out->writeAttribute('column', $column);
                    $out->writeAttribute('source', $error['source']);
                    $out->writeAttribute('severity', $error['severity']);
                    $out->writeAttribute('fixable', (int) $error['fixable']);
                    $out->text($error['message']);
                    $out->endElement();
                }
            }
        }
        $out->endElement();
        // Remove the start of the document because we will
        // add that manually later. We only have it in here to
        // properly set the encoding.
        $content = $out->flush();
        if (strpos($content, \PHP_EOL) !== \false) {
            $content = (string) substr($content, strpos($content, \PHP_EOL) + strlen(\PHP_EOL));
        } elseif (strpos($content, "\n") !== \false) {
            $content = (string) substr($content, strpos($content, "\n") + 1);
        }
        echo $content;
        return \true;
    }
    /**
     * Prints all violations for processed files, in a proprietary XML format.
     *
     * @param string $cachedData    Any partial report data that was returned from
     *                              generateFileReport during the run.
     * @param int    $totalFiles    Total number of files processed during the run.
     * @param int    $totalErrors   Total number of errors found during the run.
     * @param int    $totalWarnings Total number of warnings found during the run.
     * @param int    $totalFixable  Total number of problems that can be fixed.
     * @param bool   $showSources   Show sources?
     * @param int    $width         Maximum allowed line width.
     * @param bool   $interactive   Are we running in interactive mode?
     * @param bool   $toScreen      Is the report being printed to screen?
     *
     * @return void
     */
    public function generate(string $cachedData, int $totalFiles, int $totalErrors, int $totalWarnings, int $totalFixable, bool $showSources = \false, int $width = 80, bool $interactive = \false, bool $toScreen = \true)
    {
        echo '<?xml version="1.0" encoding="UTF-8"?>' . \PHP_EOL;
        echo '<phpcs version="' . Config::VERSION . '">' . \PHP_EOL;
        echo $cachedData;
        echo '</phpcs>' . \PHP_EOL;
    }
}
