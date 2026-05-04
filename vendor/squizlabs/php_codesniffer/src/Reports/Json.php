<?php

/**
 * JSON report for PHP_CodeSniffer.
 *
 * @author    Jeffrey Fisher <jeffslofish@gmail.com>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Reports;

use PHP_CodeSniffer\Files\File;
class Json implements \PHP_CodeSniffer\Reports\Report
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
        $filename = str_replace('\\', '\\\\', $report['filename']);
        $filename = str_replace('"', '\"', $filename);
        $filename = str_replace('/', '\/', $filename);
        echo '"' . $filename . '":{';
        echo '"errors":' . $report['errors'] . ',"warnings":' . $report['warnings'] . ',"messages":[';
        $messages = '';
        foreach ($report['messages'] as $line => $lineErrors) {
            foreach ($lineErrors as $column => $colErrors) {
                foreach ($colErrors as $error) {
                    $error['message'] = str_replace("\n", '\n', $error['message']);
                    $error['message'] = str_replace("\r", '\r', $error['message']);
                    $error['message'] = str_replace("\t", '\t', $error['message']);
                    $fixable = \false;
                    if ($error['fixable'] === \true) {
                        $fixable = \true;
                    }
                    $messagesObject = (object) $error;
                    $messagesObject->line = $line;
                    $messagesObject->column = $column;
                    $messagesObject->fixable = $fixable;
                    $messages .= json_encode($messagesObject) . ',';
                }
            }
        }
        echo rtrim($messages, ',');
        echo ']},';
        return \true;
    }
    /**
     * Generates a JSON report.
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
        echo '{"totals":{"errors":' . $totalErrors . ',"warnings":' . $totalWarnings . ',"fixable":' . $totalFixable . '},"files":{';
        echo rtrim($cachedData, ',');
        echo '}}' . \PHP_EOL;
    }
}
