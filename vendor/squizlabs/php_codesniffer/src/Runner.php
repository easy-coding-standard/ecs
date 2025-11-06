<?php

/**
 * Responsible for running PHPCS and PHPCBF.
 *
 * After creating an object of this class, you probably just want to
 * call runPHPCS() or runPHPCBF().
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer;

use Exception;
use InvalidArgumentException;
use PHP_CodeSniffer\Exceptions\DeepExitException;
use PHP_CodeSniffer\Exceptions\RuntimeException;
use PHP_CodeSniffer\Files\DummyFile;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Files\FileList;
use PHP_CodeSniffer\Util\Cache;
use PHP_CodeSniffer\Util\Common;
use PHP_CodeSniffer\Util\ExitCode;
use PHP_CodeSniffer\Util\Standards;
use PHP_CodeSniffer\Util\Timing;
use PHP_CodeSniffer\Util\Tokens;
use PHP_CodeSniffer\Util\Writers\StatusWriter;
class Runner
{
    /**
     * The config data for the run.
     *
     * @var \PHP_CodeSniffer\Config
     */
    public $config = null;
    /**
     * The ruleset used for the run.
     *
     * @var \PHP_CodeSniffer\Ruleset
     */
    public $ruleset = null;
    /**
     * The reporter used for generating reports after the run.
     *
     * @var \PHP_CodeSniffer\Reporter
     */
    public $reporter = null;
    /**
     * Run the PHPCS script.
     *
     * @return int
     */
    public function runPHPCS()
    {
        $this->registerOutOfMemoryShutdownMessage('phpcs');
        try {
            Timing::startTiming();
            if (\defined('PHP_CODESNIFFER_CBF') === \false) {
                \define('PHP_CODESNIFFER_CBF', \false);
            }
            // Creating the Config object populates it with all required settings
            // based on the CLI arguments provided to the script and any config
            // values the user has set.
            $this->config = new \PHP_CodeSniffer\Config();
            // Init the run and load the rulesets to set additional config vars.
            $this->init();
            // Print a list of sniffs in each of the supplied standards.
            // We fudge the config here so that each standard is explained in isolation.
            if ($this->config->explain === \true) {
                $standards = $this->config->standards;
                foreach ($standards as $standard) {
                    $this->config->standards = [$standard];
                    $ruleset = new \PHP_CodeSniffer\Ruleset($this->config);
                    $ruleset->explain();
                }
                return 0;
            }
            // Generate documentation for each of the supplied standards.
            if ($this->config->generator !== null) {
                $standards = $this->config->standards;
                foreach ($standards as $standard) {
                    $this->config->standards = [$standard];
                    $ruleset = new \PHP_CodeSniffer\Ruleset($this->config);
                    $class = 'PHP_CodeSniffer\\Generators\\' . $this->config->generator;
                    $generator = new $class($ruleset);
                    $generator->generate();
                }
                return 0;
            }
            // Other report formats don't really make sense in interactive mode
            // so we hard-code the full report here and when outputting.
            // We also ensure parallel processing is off because we need to do one file at a time.
            if ($this->config->interactive === \true) {
                $this->config->reports = ['full' => null];
                $this->config->parallel = 1;
                $this->config->showProgress = \false;
            }
            // Disable caching if we are processing STDIN as we can't be 100%
            // sure where the file came from or if it will change in the future.
            if ($this->config->stdin === \true) {
                $this->config->cache = \false;
            }
            $this->run();
            // Print all the reports for this run.
            $this->reporter->printReports();
            if ($this->config->quiet === \false) {
                Timing::printRunTime();
            }
        } catch (DeepExitException $e) {
            $exitCode = $e->getCode();
            $message = $e->getMessage();
            if ($message !== '') {
                if ($exitCode === 0) {
                    echo $e->getMessage();
                } else {
                    StatusWriter::write($e->getMessage(), 0, 0);
                }
            }
            return $exitCode;
        }
        return ExitCode::calculate($this->reporter);
    }
    /**
     * Run the PHPCBF script.
     *
     * @return int
     */
    public function runPHPCBF()
    {
        $this->registerOutOfMemoryShutdownMessage('phpcbf');
        if (\defined('PHP_CODESNIFFER_CBF') === \false) {
            \define('PHP_CODESNIFFER_CBF', \true);
        }
        try {
            Timing::startTiming();
            // Creating the Config object populates it with all required settings
            // based on the CLI arguments provided to the script and any config
            // values the user has set.
            $this->config = new \PHP_CodeSniffer\Config();
            // When processing STDIN, we can't output anything to the screen
            // or it will end up mixed in with the file output.
            if ($this->config->stdin === \true) {
                $this->config->verbosity = 0;
            }
            // Init the run and load the rulesets to set additional config vars.
            $this->init();
            // When processing STDIN, we only process one file at a time and
            // we don't process all the way through, so we can't use the parallel
            // running system.
            if ($this->config->stdin === \true) {
                $this->config->parallel = 1;
            }
            // Override some of the command line settings that might break the fixes.
            $this->config->generator = null;
            $this->config->explain = \false;
            $this->config->interactive = \false;
            $this->config->cache = \false;
            $this->config->showSources = \false;
            $this->config->recordErrors = \false;
            $this->config->reportFile = null;
            // Only use the "Cbf" report, but allow for the Performance report as well.
            $originalReports = \array_change_key_case($this->config->reports, \CASE_LOWER);
            $newReports = ['cbf' => null];
            if (\array_key_exists('performance', $originalReports) === \true) {
                $newReports['performance'] = $originalReports['performance'];
            }
            $this->config->reports = $newReports;
            // If a standard tries to set command line arguments itself, some
            // may be blocked because PHPCBF is running, so stop the script
            // dying if any are found.
            $this->config->dieOnUnknownArg = \false;
            $this->run();
            $this->reporter->printReports();
            if ($this->config->quiet === \false) {
                StatusWriter::writeNewline();
                Timing::printRunTime();
            }
        } catch (DeepExitException $e) {
            $exitCode = $e->getCode();
            $message = $e->getMessage();
            if ($message !== '') {
                if ($exitCode === 0) {
                    echo $e->getMessage();
                } else {
                    StatusWriter::write($e->getMessage(), 0, 0);
                }
            }
            return $exitCode;
        }
        return ExitCode::calculate($this->reporter);
    }
    /**
     * Init the rulesets and other high-level settings.
     *
     * @return void
     * @throws \PHP_CodeSniffer\Exceptions\DeepExitException If a referenced standard is not installed.
     */
    public function init()
    {
        if (\defined('PHP_CODESNIFFER_CBF') === \false) {
            \define('PHP_CODESNIFFER_CBF', \false);
        }
        // Disable the PCRE JIT as this caused issues with parallel running.
        \ini_set('pcre.jit', \false);
        // Check that the standards are valid.
        foreach ($this->config->standards as $standard) {
            if (Standards::isInstalledStandard($standard) === \false) {
                // They didn't select a valid coding standard, so help them
                // out by letting them know which standards are installed.
                $error = 'ERROR: the "' . $standard . '" coding standard is not installed.' . \PHP_EOL . \PHP_EOL;
                $error .= Standards::prepareInstalledStandardsForDisplay() . \PHP_EOL;
                throw new DeepExitException($error, ExitCode::PROCESS_ERROR);
            }
        }
        // Saves passing the Config object into other objects that only need
        // the verbosity flag for debug output.
        if (\defined('PHP_CODESNIFFER_VERBOSITY') === \false) {
            \define('PHP_CODESNIFFER_VERBOSITY', $this->config->verbosity);
        }
        // Create this class so it is autoloaded and sets up a bunch
        // of PHP_CodeSniffer-specific token type constants.
        new Tokens();
        // Allow autoloading of custom files inside installed standards.
        $installedStandards = Standards::getInstalledStandardDetails();
        foreach ($installedStandards as $details) {
            \PHP_CodeSniffer\Autoload::addSearchPath($details['path'], $details['namespace']);
        }
        // The ruleset contains all the information about how the files
        // should be checked and/or fixed.
        try {
            $this->ruleset = new \PHP_CodeSniffer\Ruleset($this->config);
            if ($this->ruleset->hasSniffDeprecations() === \true) {
                $this->ruleset->showSniffDeprecations();
            }
        } catch (RuntimeException $e) {
            $error = \rtrim($e->getMessage(), "\r\n") . \PHP_EOL . \PHP_EOL;
            $error .= $this->config->printShortUsage(\true);
            throw new DeepExitException($error, ExitCode::PROCESS_ERROR);
        }
    }
    /**
     * Performs the run.
     *
     * @return void
     *
     * @throws \PHP_CodeSniffer\Exceptions\DeepExitException
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException
     */
    private function run()
    {
        // The class that manages all reporters for the run.
        $this->reporter = new \PHP_CodeSniffer\Reporter($this->config);
        // Include bootstrap files.
        foreach ($this->config->bootstrap as $bootstrap) {
            include $bootstrap;
        }
        if ($this->config->stdin === \true) {
            $fileContents = $this->config->stdinContent;
            if ($fileContents === null) {
                $handle = \fopen('php://stdin', 'r');
                \stream_set_blocking($handle, \true);
                $fileContents = \stream_get_contents($handle);
                \fclose($handle);
            }
            $todo = new FileList($this->config, $this->ruleset);
            $dummy = new DummyFile($fileContents, $this->ruleset, $this->config);
            $todo->addFile($dummy->path, $dummy);
        } else {
            if (empty($this->config->files) === \true) {
                $error = 'ERROR: You must supply at least one file or directory to process.' . \PHP_EOL . \PHP_EOL;
                $error .= $this->config->printShortUsage(\true);
                throw new DeepExitException($error, ExitCode::PROCESS_ERROR);
            }
            if (\PHP_CODESNIFFER_VERBOSITY > 0) {
                StatusWriter::write('Creating file list... ', 0, 0);
            }
            $todo = new FileList($this->config, $this->ruleset);
            if (\PHP_CODESNIFFER_VERBOSITY > 0) {
                $numFiles = \count($todo);
                StatusWriter::write("DONE ({$numFiles} files in queue)");
            }
            if ($this->config->cache === \true) {
                if (\PHP_CODESNIFFER_VERBOSITY > 0) {
                    StatusWriter::write('Loading cache... ', 0, 0);
                }
                Cache::load($this->ruleset, $this->config);
                if (\PHP_CODESNIFFER_VERBOSITY > 0) {
                    $size = Cache::getSize();
                    StatusWriter::write("DONE ({$size} files in cache)");
                }
            }
        }
        $numFiles = \count($todo);
        if ($numFiles === 0) {
            $error = 'ERROR: No files were checked.' . \PHP_EOL;
            $error .= 'All specified files were excluded or did not match filtering rules.' . \PHP_EOL . \PHP_EOL;
            throw new DeepExitException($error, ExitCode::PROCESS_ERROR);
        }
        // Turn all sniff errors into exceptions.
        \set_error_handler([$this, 'handleErrors']);
        // If verbosity is too high, turn off parallelism so the
        // debug output is clean.
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            $this->config->parallel = 1;
        }
        // If the PCNTL extension isn't installed, we can't fork.
        if (\function_exists('pcntl_fork') === \false) {
            $this->config->parallel = 1;
        }
        $lastDir = '';
        if ($this->config->parallel === 1) {
            // Running normally.
            $numProcessed = 0;
            foreach ($todo as $path => $file) {
                if ($file->ignored === \false) {
                    $currDir = \dirname($path);
                    if ($lastDir !== $currDir) {
                        if (\PHP_CODESNIFFER_VERBOSITY > 0) {
                            StatusWriter::write('Changing into directory ' . Common::stripBasepath($currDir, $this->config->basepath));
                        }
                        $lastDir = $currDir;
                    }
                    $this->processFile($file);
                } elseif (\PHP_CODESNIFFER_VERBOSITY > 0) {
                    StatusWriter::write('Skipping ' . \basename($file->path));
                }
                $numProcessed++;
                $this->printProgress($file, $numFiles, $numProcessed);
            }
        } else {
            // Batching and forking.
            $childProcs = [];
            $numPerBatch = \ceil($numFiles / $this->config->parallel);
            for ($batch = 0; $batch < $this->config->parallel; $batch++) {
                $startAt = $batch * $numPerBatch;
                if ($startAt >= $numFiles) {
                    break;
                }
                $endAt = $startAt + $numPerBatch;
                if ($endAt > $numFiles) {
                    $endAt = $numFiles;
                }
                $childOutFilename = \tempnam(\sys_get_temp_dir(), 'phpcs-child');
                $pid = \pcntl_fork();
                if ($pid === -1) {
                    throw new RuntimeException('Failed to create child process');
                } elseif ($pid !== 0) {
                    $childProcs[$pid] = $childOutFilename;
                } else {
                    // Move forward to the start of the batch.
                    $todo->rewind();
                    for ($i = 0; $i < $startAt; $i++) {
                        $todo->next();
                    }
                    // Reset the reporter to make sure only figures from this
                    // file batch are recorded.
                    $this->reporter->totalFiles = 0;
                    $this->reporter->totalErrors = 0;
                    $this->reporter->totalWarnings = 0;
                    $this->reporter->totalFixableErrors = 0;
                    $this->reporter->totalFixableWarnings = 0;
                    $this->reporter->totalFixedErrors = 0;
                    $this->reporter->totalFixedWarnings = 0;
                    // Process the files.
                    $pathsProcessed = [];
                    \ob_start();
                    for ($i = $startAt; $i < $endAt; $i++) {
                        $path = $todo->key();
                        $file = $todo->current();
                        if ($file->ignored === \true) {
                            $todo->next();
                            continue;
                        }
                        $currDir = \dirname($path);
                        if ($lastDir !== $currDir) {
                            if (\PHP_CODESNIFFER_VERBOSITY > 0) {
                                StatusWriter::write('Changing into directory ' . Common::stripBasepath($currDir, $this->config->basepath));
                            }
                            $lastDir = $currDir;
                        }
                        $this->processFile($file);
                        $pathsProcessed[] = $path;
                        $todo->next();
                    }
                    $debugOutput = \ob_get_contents();
                    \ob_end_clean();
                    // Write information about the run to the filesystem
                    // so it can be picked up by the main process.
                    $childOutput = ['totalFiles' => $this->reporter->totalFiles, 'totalErrors' => $this->reporter->totalErrors, 'totalWarnings' => $this->reporter->totalWarnings, 'totalFixableErrors' => $this->reporter->totalFixableErrors, 'totalFixableWarnings' => $this->reporter->totalFixableWarnings, 'totalFixedErrors' => $this->reporter->totalFixedErrors, 'totalFixedWarnings' => $this->reporter->totalFixedWarnings];
                    $output = '<' . '?php' . "\n" . ' $childOutput = ';
                    $output .= \var_export($childOutput, \true);
                    $output .= ";\n\$debugOutput = ";
                    $output .= \var_export($debugOutput, \true);
                    if ($this->config->cache === \true) {
                        $childCache = [];
                        foreach ($pathsProcessed as $path) {
                            $childCache[$path] = Cache::get($path);
                        }
                        $output .= ";\n\$childCache = ";
                        $output .= \var_export($childCache, \true);
                    }
                    $output .= ";\n?" . '>';
                    \file_put_contents($childOutFilename, $output);
                    exit;
                }
            }
            $success = $this->processChildProcs($childProcs);
            if ($success === \false) {
                throw new RuntimeException('One or more child processes failed to run');
            }
        }
        \restore_error_handler();
        if (\PHP_CODESNIFFER_VERBOSITY === 0 && $this->config->interactive === \false && $this->config->showProgress === \true) {
            StatusWriter::writeNewline(2);
        }
        if ($this->config->cache === \true) {
            Cache::save();
        }
    }
    /**
     * Converts all PHP errors into exceptions.
     *
     * This method forces a sniff to stop processing if it is not
     * able to handle a specific piece of code, instead of continuing
     * and potentially getting into a loop.
     *
     * @param int    $code    The level of error raised.
     * @param string $message The error message.
     * @param string $file    The path of the file that raised the error.
     * @param int    $line    The line number the error was raised at.
     *
     * @return bool
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException
     */
    public function handleErrors(int $code, string $message, string $file, int $line)
    {
        if ((\error_reporting() & $code) === 0) {
            // This type of error is being muted.
            return \true;
        }
        throw new RuntimeException("{$message} in {$file} on line {$line}");
    }
    /**
     * Processes a single file, including checking and fixing.
     *
     * @param \PHP_CodeSniffer\Files\File $file The file to be processed.
     *
     * @return void
     * @throws \PHP_CodeSniffer\Exceptions\DeepExitException
     */
    public function processFile(File $file)
    {
        if (\PHP_CODESNIFFER_VERBOSITY > 0) {
            $startTime = \microtime(\true);
            $newlines = 0;
            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                $newlines = 1;
            }
            StatusWriter::write('Processing ' . \basename($file->path) . ' ', 0, $newlines);
        }
        try {
            $file->process();
            if (\PHP_CODESNIFFER_VERBOSITY > 0) {
                StatusWriter::write('DONE in ' . Timing::getHumanReadableDuration(Timing::getDurationSince($startTime)), 0, 0);
                if (\PHP_CODESNIFFER_CBF === \true) {
                    $errors = $file->getFixableErrorCount();
                    $warnings = $file->getFixableWarningCount();
                    StatusWriter::write(" ({$errors} fixable errors, {$warnings} fixable warnings)");
                } else {
                    $errors = $file->getErrorCount();
                    $warnings = $file->getWarningCount();
                    StatusWriter::write(" ({$errors} errors, {$warnings} warnings)");
                }
            }
        } catch (Exception $e) {
            $error = 'An error occurred during processing; checking has been aborted. The error message was: ' . $e->getMessage();
            // Determine which sniff caused the error.
            $sniffStack = null;
            $nextStack = null;
            foreach ($e->getTrace() as $step) {
                if (isset($step['file']) === \false) {
                    continue;
                }
                if (empty($sniffStack) === \false) {
                    $nextStack = $step;
                    break;
                }
                if (\substr($step['file'], -9) === 'Sniff.php') {
                    $sniffStack = $step;
                    continue;
                }
            }
            if (empty($sniffStack) === \false) {
                $sniffCode = '';
                try {
                    if (empty($nextStack) === \false && isset($nextStack['class']) === \true) {
                        $sniffCode = 'the ' . Common::getSniffCode($nextStack['class']) . ' sniff';
                    }
                } catch (InvalidArgumentException $e) {
                    // Sniff code could not be determined. This may be an abstract sniff class.
                }
                if ($sniffCode === '') {
                    $sniffCode = (string) \substr(\strrchr(\str_replace('\\', '/', $sniffStack['file']), '/'), 1);
                }
                $error .= \sprintf(\PHP_EOL . 'The error originated in %s on line %s.', $sniffCode, $sniffStack['line']);
            }
            $file->addErrorOnLine($error, 1, 'Internal.Exception');
        }
        $this->reporter->cacheFileReport($file);
        if ($this->config->interactive === \true) {
            /*
                Running interactively.
                Print the error report for the current file and then wait for user input.
            */
            // Get current violations and then clear the list to make sure
            // we only print violations for a single file each time.
            $numErrors = null;
            while ($numErrors !== 0) {
                $numErrors = $file->getErrorCount() + $file->getWarningCount();
                if ($numErrors === 0) {
                    continue;
                }
                $this->reporter->printReport('full');
                echo '<ENTER> to recheck, [s] to skip or [q] to quit : ';
                $input = \fgets(\STDIN);
                $input = \trim($input);
                switch ($input) {
                    case 's':
                        break 2;
                    case 'q':
                        // User request to "quit": exit code should be 0.
                        throw new DeepExitException('', ExitCode::OKAY);
                    default:
                        // Repopulate the sniffs because some of them save their state
                        // and only clear it when the file changes, but we are rechecking
                        // the same file.
                        $file->ruleset->populateTokenListeners();
                        $file->reloadContent();
                        $file->process();
                        $this->reporter->cacheFileReport($file);
                        break;
                }
            }
        }
        // Clean up the file to save (a lot of) memory.
        $file->cleanUp();
    }
    /**
     * Waits for child processes to complete and cleans up after them.
     *
     * The reporting information returned by each child process is merged
     * into the main reporter class.
     *
     * @param array<int, string> $childProcs An array of child processes to wait for.
     *
     * @return bool
     */
    private function processChildProcs(array $childProcs)
    {
        $numProcessed = 0;
        $totalBatches = \count($childProcs);
        $success = \true;
        while (\count($childProcs) > 0) {
            $pid = \pcntl_waitpid(0, $status);
            if ($pid <= 0 || isset($childProcs[$pid]) === \false) {
                // No child or a child with an unmanaged PID was returned.
                continue;
            }
            $childProcessStatus = \pcntl_wexitstatus($status);
            if ($childProcessStatus !== 0) {
                $success = \false;
            }
            $out = $childProcs[$pid];
            unset($childProcs[$pid]);
            if (\file_exists($out) === \false) {
                continue;
            }
            include $out;
            \unlink($out);
            $numProcessed++;
            if (isset($childOutput) === \false) {
                // The child process died, so the run has failed.
                $file = new DummyFile('', $this->ruleset, $this->config);
                $file->setErrorCounts(1, 0, 0, 0, 0, 0);
                $this->printProgress($file, $totalBatches, $numProcessed);
                $success = \false;
                continue;
            }
            $this->reporter->totalFiles += $childOutput['totalFiles'];
            $this->reporter->totalErrors += $childOutput['totalErrors'];
            $this->reporter->totalWarnings += $childOutput['totalWarnings'];
            $this->reporter->totalFixableErrors += $childOutput['totalFixableErrors'];
            $this->reporter->totalFixableWarnings += $childOutput['totalFixableWarnings'];
            $this->reporter->totalFixedErrors += $childOutput['totalFixedErrors'];
            $this->reporter->totalFixedWarnings += $childOutput['totalFixedWarnings'];
            if (isset($debugOutput) === \true) {
                echo $debugOutput;
            }
            if (isset($childCache) === \true) {
                foreach ($childCache as $path => $cache) {
                    Cache::set($path, $cache);
                }
            }
            // Fake a processed file so we can print progress output for the batch.
            $file = new DummyFile('', $this->ruleset, $this->config);
            $file->setErrorCounts($childOutput['totalErrors'], $childOutput['totalWarnings'], $childOutput['totalFixableErrors'], $childOutput['totalFixableWarnings'], $childOutput['totalFixedErrors'], $childOutput['totalFixedWarnings']);
            $this->printProgress($file, $totalBatches, $numProcessed);
        }
        return $success;
    }
    /**
     * Print progress information for a single processed file.
     *
     * @param \PHP_CodeSniffer\Files\File $file         The file that was processed.
     * @param int                         $numFiles     The total number of files to process.
     * @param int                         $numProcessed The number of files that have been processed,
     *                                                  including this one.
     *
     * @return void
     */
    public function printProgress(File $file, int $numFiles, int $numProcessed)
    {
        if (\PHP_CODESNIFFER_VERBOSITY > 0 || $this->config->showProgress === \false) {
            return;
        }
        $showColors = $this->config->colors;
        $colorOpen = '';
        $progressDot = '.';
        $colorClose = '';
        // Show progress information.
        if ($file->ignored === \true) {
            $progressDot = 'S';
        } else {
            $errors = $file->getErrorCount();
            $warnings = $file->getWarningCount();
            $fixable = $file->getFixableCount();
            $fixed = $file->getFixedErrorCount() + $file->getFixedWarningCount();
            if (\PHP_CODESNIFFER_CBF === \true) {
                // Files with fixed errors or warnings are F (green).
                // Files with unfixable errors or warnings are E (red).
                // Files with no errors or warnings are . (black).
                if ($fixable > 0) {
                    $progressDot = 'E';
                    if ($showColors === \true) {
                        $colorOpen = "\x1b[31m";
                        $colorClose = "\x1b[0m";
                    }
                } elseif ($fixed > 0) {
                    $progressDot = 'F';
                    if ($showColors === \true) {
                        $colorOpen = "\x1b[32m";
                        $colorClose = "\x1b[0m";
                    }
                }
            } else {
                // Files with errors are E (red).
                // Files with fixable errors are E (green).
                // Files with warnings are W (yellow).
                // Files with fixable warnings are W (green).
                // Files with no errors or warnings are . (black).
                if ($errors > 0) {
                    $progressDot = 'E';
                    if ($showColors === \true) {
                        if ($fixable > 0) {
                            $colorOpen = "\x1b[32m";
                        } else {
                            $colorOpen = "\x1b[31m";
                        }
                        $colorClose = "\x1b[0m";
                    }
                } elseif ($warnings > 0) {
                    $progressDot = 'W';
                    if ($showColors === \true) {
                        if ($fixable > 0) {
                            $colorOpen = "\x1b[32m";
                        } else {
                            $colorOpen = "\x1b[33m";
                        }
                        $colorClose = "\x1b[0m";
                    }
                }
            }
        }
        StatusWriter::write($colorOpen . $progressDot . $colorClose, 0, 0);
        $numPerLine = 60;
        if ($numProcessed !== $numFiles && $numProcessed % $numPerLine !== 0) {
            return;
        }
        $percent = \round($numProcessed / $numFiles * 100);
        $padding = \strlen($numFiles) - \strlen($numProcessed);
        if ($numProcessed === $numFiles && $numFiles > $numPerLine && $numProcessed % $numPerLine !== 0) {
            $padding += $numPerLine - ($numFiles - \floor($numFiles / $numPerLine) * $numPerLine);
        }
        StatusWriter::write(\str_repeat(' ', $padding) . " {$numProcessed} / {$numFiles} ({$percent}%)");
    }
    /**
     * Registers a PHP shutdown function to provide a more informative out of memory error.
     *
     * @param string $command The command which was used to initiate the PHPCS run.
     *
     * @return void
     */
    private function registerOutOfMemoryShutdownMessage(string $command)
    {
        // Allocate all needed memory beforehand as much as possible.
        $errorMsg = \PHP_EOL . 'The PHP_CodeSniffer "%1$s" command ran out of memory.' . \PHP_EOL;
        $errorMsg .= 'Either raise the "memory_limit" of PHP in the php.ini file or raise the memory limit at runtime' . \PHP_EOL;
        $errorMsg .= 'using `%1$s -d memory_limit=512M` (replace 512M with the desired memory limit).' . \PHP_EOL;
        $errorMsg = \sprintf($errorMsg, $command);
        $memoryError = 'Allowed memory size of';
        $errorArray = ['type' => 42, 'message' => 'Some random dummy string to take up memory and take up some more memory and some more', 'file' => 'Another random string, which would be a filename this time. Should be relatively long to allow for deeply nested files', 'line' => 31427];
        \register_shutdown_function(static function () use($errorMsg, $memoryError, $errorArray) {
            $errorArray = \error_get_last();
            if (\is_array($errorArray) === \true && \strpos($errorArray['message'], $memoryError) !== \false) {
                \fwrite(\STDERR, $errorMsg);
            }
        });
    }
}
