<?php

/**
 * An abstract class that all sniff unit tests must extend.
 *
 * A sniff unit test checks a .inc file for expected violations of a single
 * coding standard. Expected errors and warnings that are not found, or
 * warnings and errors that are not expected, are considered test failures.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Tests\Standards;

use DirectoryIterator;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Exceptions\RuntimeException;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Ruleset;
use PHP_CodeSniffer\Tests\ConfigDouble;
use PHP_CodeSniffer\Util\Common;
use ECSPrefix202605\PHPUnit\Framework\TestCase;
use ReflectionClass;
abstract class AbstractSniffTestCase extends TestCase
{
    /**
     * Ruleset template with placeholders.
     *
     * @var string
     */
    private const RULESET_TEMPLATE = <<<'TEMPLATE'
<?xml version="1.0"?>
<ruleset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" name="[STANDARDNAME]" xsi:noNamespaceSchemaLocation="../../phpcs.xsd">
    <description>Temporary ruleset used by the AbstractSniffUnitTest class.</description>

    <rule ref="[SNIFFFILEREF]"/>

</ruleset>
TEMPLATE;
    /**
     * Placeholders used in the ruleset template which need to be replaced.
     *
     * @var array<string>
     */
    private const SEARCH_FOR = ['[STANDARDNAME]', '[SNIFFFILEREF]'];
    /**
     * Location where the temporary ruleset file will be saved.
     *
     * @var string
     */
    private const RULESET_FILENAME = __DIR__ . '/sniffStnd.xml';
    /**
     * Cache for the Config object.
     *
     * @var \PHP_CodeSniffer\Tests\ConfigDouble
     */
    private static $config;
    /**
     * Extensions to disregard when gathering the test files.
     *
     * @var array<string, string>
     */
    private $ignoreExtensions = ['php' => 'php', 'fixed' => 'fixed', 'bak' => 'bak', 'orig' => 'orig'];
    /**
     * Clean up temporary ruleset file.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        @unlink(self::RULESET_FILENAME);
    }
    /**
     * Get a list of all test files to check.
     *
     * These will have the same base as the sniff name but different extensions.
     * We ignore the .php file as it is the test class.
     *
     * @param string $testFileBase The base path that the unit tests files will have.
     *
     * @return string[]
     */
    protected function getTestFiles(string $testFileBase)
    {
        $testFiles = [];
        $dir = dirname($testFileBase);
        $di = new DirectoryIterator($dir);
        foreach ($di as $file) {
            $path = $file->getPathname();
            if (substr($path, 0, strlen($testFileBase)) === $testFileBase) {
                $extension = $file->getExtension();
                if (isset($this->ignoreExtensions[$extension]) === \false) {
                    $testFiles[] = $path;
                }
            }
        }
        // Put them in order.
        sort($testFiles, \SORT_NATURAL);
        return $testFiles;
    }
    /**
     * Should this test be skipped for some reason.
     *
     * @return boolean
     */
    protected function shouldSkipTest()
    {
        return \false;
    }
    /**
     * Tests the extending classes Sniff class.
     *
     * @return void
     *
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException
     */
    final public function testSniff()
    {
        // Skip this test if we can't run in this environment.
        if ($this->shouldSkipTest() === \true) {
            $this->markTestSkipped();
        }
        $sniffCode = Common::getSniffCode(static::class);
        $sniffCodeParts = explode('.', $sniffCode);
        $standardName = $sniffCodeParts[0];
        $testFileBase = (new ReflectionClass(static::class))->getFileName();
        $testFileBase = substr($testFileBase, 0, -3);
        // Get a list of all test files to check.
        $testFiles = $this->getTestFiles($testFileBase);
        if (empty($testFiles) === \true) {
            $this->markTestIncomplete('No test case files found for ' . static::class);
        }
        $sniffFile = preg_replace('`[/\\\\]Tests[/\\\\]`', \DIRECTORY_SEPARATOR . 'Sniffs' . \DIRECTORY_SEPARATOR, $testFileBase);
        $sniffFile = str_replace('UnitTest.', 'Sniff.php', $sniffFile);
        if (file_exists($sniffFile) === \false) {
            $this->fail(sprintf('ERROR: Sniff file %s for test %s does not appear to exist', $sniffFile, static::class));
        }
        $replacements = [$standardName, $sniffFile];
        $rulesetContents = str_replace(self::SEARCH_FOR, $replacements, self::RULESET_TEMPLATE);
        if (file_put_contents(self::RULESET_FILENAME, $rulesetContents) === \false) {
            throw new RuntimeException('Failed to write custom ruleset file');
        }
        if (isset(self::$config) === \true) {
            $config = self::$config;
        } else {
            $config = new ConfigDouble();
            $config->cache = \false;
            self::$config = $config;
        }
        $config->standards = [self::RULESET_FILENAME];
        $config->sniffs = [$sniffCode];
        $config->ignored = [];
        $ruleset = new Ruleset($config);
        $failureMessages = [];
        foreach ($testFiles as $testFile) {
            $filename = basename($testFile);
            $oldConfig = $config->getSettings();
            try {
                $this->setCliValues($filename, $config);
                $phpcsFile = new LocalFile($testFile, $ruleset, $config);
                $phpcsFile->process();
            } catch (RuntimeException $e) {
                $this->fail('An unexpected exception has been caught: ' . $e->getMessage());
            }
            $failures = $this->generateFailureMessages($phpcsFile);
            $failureMessages = array_merge($failureMessages, $failures);
            if ($phpcsFile->getFixableCount() > 0) {
                // Attempt to fix the errors.
                $phpcsFile->fixer->fixFile();
                $fixable = $phpcsFile->getFixableCount();
                if ($fixable > 0) {
                    $failureMessages[] = "Failed to fix {$fixable} fixable violations in {$filename}";
                }
                // Check for a .fixed file to check for accuracy of fixes.
                $fixedFile = $testFile . '.fixed';
                $filename = basename($testFile);
                if (file_exists($fixedFile) === \true) {
                    if ($phpcsFile->fixer->getContents() !== file_get_contents($fixedFile)) {
                        // Only generate the (expensive) diff if a difference is expected.
                        $diff = $phpcsFile->fixer->generateDiff($fixedFile);
                        if (trim($diff) !== '') {
                            $fixedFilename = basename($fixedFile);
                            $failureMessages[] = "Fixed version of {$filename} does not match expected version in {$fixedFilename}; the diff is\n{$diff}";
                        }
                    }
                } else {
                    $diff = trim($phpcsFile->fixer->generateDiff($testFile));
                    $failureMessages[] = "Missing fixed version of {$filename} to verify the accuracy of fixes, while the sniff is making fixes against the test case file; the diff is\n{$diff}";
                }
            }
            // Restore the config.
            $config->setSettings($oldConfig);
        }
        if (empty($failureMessages) === \false) {
            $this->fail(implode(\PHP_EOL, $failureMessages));
        }
    }
    /**
     * Generate a list of test failures for a given sniffed file.
     *
     * @param \PHP_CodeSniffer\Files\LocalFile $file The file being tested.
     *
     * @return array
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException
     */
    public function generateFailureMessages(LocalFile $file)
    {
        $testFile = $file->getFilename();
        $foundErrors = $file->getErrors();
        $foundWarnings = $file->getWarnings();
        $expectedErrors = $this->getErrorList(basename($testFile));
        $expectedWarnings = $this->getWarningList(basename($testFile));
        if (is_array($expectedErrors) === \false) {
            throw new RuntimeException('getErrorList() must return an array');
        }
        if (is_array($expectedWarnings) === \false) {
            throw new RuntimeException('getWarningList() must return an array');
        }
        /*
            We merge errors and warnings together to make it easier
            to iterate over them and produce the errors string. In this way,
            we can report on errors and warnings in the same line even though
            it's not really structured to allow that.
        */
        $allProblems = [];
        $failureMessages = [];
        foreach ($foundErrors as $line => $lineErrors) {
            foreach ($lineErrors as $column => $errors) {
                if (isset($allProblems[$line]) === \false) {
                    $allProblems[$line] = ['expected_errors' => 0, 'expected_warnings' => 0, 'found_errors' => [], 'found_warnings' => []];
                }
                $foundErrorsTemp = [];
                foreach ($allProblems[$line]['found_errors'] as $foundError) {
                    $foundErrorsTemp[] = $foundError;
                }
                $errorsTemp = [];
                foreach ($errors as $foundError) {
                    $errorsTemp[] = $foundError['message'] . ' (' . $foundError['source'] . ')';
                }
                $allProblems[$line]['found_errors'] = array_merge($foundErrorsTemp, $errorsTemp);
            }
            if (isset($expectedErrors[$line]) === \true) {
                $allProblems[$line]['expected_errors'] = $expectedErrors[$line];
            } else {
                $allProblems[$line]['expected_errors'] = 0;
            }
            unset($expectedErrors[$line]);
        }
        foreach ($expectedErrors as $line => $numErrors) {
            if (isset($allProblems[$line]) === \false) {
                $allProblems[$line] = ['expected_errors' => 0, 'expected_warnings' => 0, 'found_errors' => [], 'found_warnings' => []];
            }
            $allProblems[$line]['expected_errors'] = $numErrors;
        }
        foreach ($foundWarnings as $line => $lineWarnings) {
            foreach ($lineWarnings as $column => $warnings) {
                if (isset($allProblems[$line]) === \false) {
                    $allProblems[$line] = ['expected_errors' => 0, 'expected_warnings' => 0, 'found_errors' => [], 'found_warnings' => []];
                }
                $foundWarningsTemp = [];
                foreach ($allProblems[$line]['found_warnings'] as $foundWarning) {
                    $foundWarningsTemp[] = $foundWarning;
                }
                $warningsTemp = [];
                foreach ($warnings as $warning) {
                    $warningsTemp[] = $warning['message'] . ' (' . $warning['source'] . ')';
                }
                $allProblems[$line]['found_warnings'] = array_merge($foundWarningsTemp, $warningsTemp);
            }
            if (isset($expectedWarnings[$line]) === \true) {
                $allProblems[$line]['expected_warnings'] = $expectedWarnings[$line];
            } else {
                $allProblems[$line]['expected_warnings'] = 0;
            }
            unset($expectedWarnings[$line]);
        }
        foreach ($expectedWarnings as $line => $numWarnings) {
            if (isset($allProblems[$line]) === \false) {
                $allProblems[$line] = ['expected_errors' => 0, 'expected_warnings' => 0, 'found_errors' => [], 'found_warnings' => []];
            }
            $allProblems[$line]['expected_warnings'] = $numWarnings;
        }
        // Order the messages by line number.
        ksort($allProblems);
        foreach ($allProblems as $line => $problems) {
            $numErrors = count($problems['found_errors']);
            $numWarnings = count($problems['found_warnings']);
            $expectedErrors = $problems['expected_errors'];
            $expectedWarnings = $problems['expected_warnings'];
            $errors = '';
            $foundString = '';
            if ($expectedErrors !== $numErrors || $expectedWarnings !== $numWarnings) {
                $lineMessage = "[LINE {$line}]";
                $expectedMessage = 'Expected ';
                $foundMessage = 'in ' . basename($testFile) . ' but found ';
                if ($expectedErrors !== $numErrors) {
                    $expectedMessage .= "{$expectedErrors} error(s)";
                    $foundMessage .= "{$numErrors} error(s)";
                    if ($numErrors !== 0) {
                        $foundString .= 'error(s)';
                        $errors .= implode(\PHP_EOL . ' -> ', $problems['found_errors']);
                    }
                    if ($expectedWarnings !== $numWarnings) {
                        $expectedMessage .= ' and ';
                        $foundMessage .= ' and ';
                        if ($numWarnings !== 0) {
                            if ($foundString !== '') {
                                $foundString .= ' and ';
                            }
                        }
                    }
                }
                if ($expectedWarnings !== $numWarnings) {
                    $expectedMessage .= "{$expectedWarnings} warning(s)";
                    $foundMessage .= "{$numWarnings} warning(s)";
                    if ($numWarnings !== 0) {
                        $foundString .= 'warning(s)';
                        if (empty($errors) === \false) {
                            $errors .= \PHP_EOL . ' -> ';
                        }
                        $errors .= implode(\PHP_EOL . ' -> ', $problems['found_warnings']);
                    }
                }
                $fullMessage = "{$lineMessage} {$expectedMessage} {$foundMessage}.";
                if ($errors !== '') {
                    $fullMessage .= " The {$foundString} found were:" . \PHP_EOL . " -> {$errors}";
                }
                $failureMessages[] = $fullMessage;
            }
        }
        return $failureMessages;
    }
    /**
     * Get a list of CLI values to set before the file is tested.
     *
     * @param string                  $testFile The name of the file being tested.
     * @param \PHP_CodeSniffer\Config $config   The config data for the run.
     *
     * @return void
     */
    public function setCliValues(string $testFile, Config $config)
    {
    }
    /**
     * Returns the lines where errors should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of errors that should occur on that line.
     *
     * @return array<int, int>
     */
    abstract protected function getErrorList();
    /**
     * Returns the lines where warnings should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of warnings that should occur on that line.
     *
     * @return array<int, int>
     */
    abstract protected function getWarningList();
}
