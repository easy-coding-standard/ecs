<?php

/**
 * Diff report for PHP_CodeSniffer.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Reports;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Timing;
use PHP_CodeSniffer\Util\Writers\StatusWriter;
class Diff implements \PHP_CodeSniffer\Reports\Report
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
        $errors = $phpcsFile->getFixableCount();
        if ($errors === 0) {
            return \false;
        }
        $phpcsFile->disableCaching();
        $tokens = $phpcsFile->getTokens();
        if (empty($tokens) === \true) {
            if (\PHP_CODESNIFFER_VERBOSITY === 1) {
                $startTime = microtime(\true);
                StatusWriter::write('DIFF report is parsing ' . basename($report['filename']) . ' ', 0, 0);
            } elseif (\PHP_CODESNIFFER_VERBOSITY > 1) {
                StatusWriter::write('DIFF report is forcing parse of ' . $report['filename']);
            }
            $phpcsFile->parse();
            if (\PHP_CODESNIFFER_VERBOSITY === 1) {
                StatusWriter::write('DONE in ' . Timing::getHumanReadableDuration(Timing::getDurationSince($startTime)));
            }
            $phpcsFile->fixer->startFile($phpcsFile);
        }
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            StatusWriter::write('*** START FILE FIXING ***', 1);
        }
        $fixed = $phpcsFile->fixer->fixFile();
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            StatusWriter::write('*** END FILE FIXING ***', 1);
        }
        if ($fixed === \false) {
            return \false;
        }
        $diff = $phpcsFile->fixer->generateDiff();
        if ($diff === '') {
            // Nothing to print.
            return \false;
        }
        echo $diff . \PHP_EOL;
        return \true;
    }
    /**
     * Prints all errors and warnings for each file processed.
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
        echo $cachedData;
        if ($toScreen === \true && $cachedData !== '') {
            echo \PHP_EOL;
        }
    }
}
