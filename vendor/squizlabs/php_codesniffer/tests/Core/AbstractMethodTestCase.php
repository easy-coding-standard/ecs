<?php

/**
 * Base class to use when testing utility methods.
 *
 * @author    Juliette Reinders Folmer <phpcs_nospam@adviesenzo.nl>
 * @copyright 2018-2019 Juliette Reinders Folmer. All rights reserved.
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Tests\Core;

use Exception;
use PHP_CodeSniffer\Exceptions\RuntimeException;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Ruleset;
use PHP_CodeSniffer\Tests\ConfigDouble;
use ECSPrefix202601\PHPUnit\Framework\TestCase;
abstract class AbstractMethodTestCase extends TestCase
{
    /**
     * The tab width setting to use when tokenizing the file.
     *
     * This allows for test case files to use a different tab width than the default.
     *
     * @var integer
     */
    protected static $tabWidth = 4;
    /**
     * The \PHP_CodeSniffer\Files\File object containing the parsed contents of the test case file.
     *
     * @var \PHP_CodeSniffer\Files\File
     */
    protected static $phpcsFile;
    /**
     * Initialize & tokenize \PHP_CodeSniffer\Files\File with code from the test case file.
     *
     * The test case file for a unit test class has to be in the same directory
     * directory and use the same file name as the test class, using the .inc extension.
     *
     * @return void
     */
    public static function setUpBeforeClass() : void
    {
        $_SERVER['argv'] = [];
        $config = new ConfigDouble();
        // Also set a tab-width to enable testing tab-replaced vs `orig_content`.
        $config->tabWidth = static::$tabWidth;
        $ruleset = new Ruleset($config);
        // Default to a file with the same name as the test class. Extension is property based.
        $relativeCN = \str_replace(__NAMESPACE__, '', static::class);
        $relativePath = \str_replace('\\', \DIRECTORY_SEPARATOR, $relativeCN);
        $pathToTestFile = \realpath(__DIR__) . $relativePath . '.inc';
        self::$phpcsFile = new LocalFile($pathToTestFile, $ruleset, $config);
        self::$phpcsFile->parse();
    }
    /**
     * Clean up after finished test by resetting all static properties on the class to their default values.
     *
     * @return void
     */
    public static function tearDownAfterClass() : void
    {
        // Explicitly trigger __destruct() on the ConfigDouble to reset the Config statics.
        // The explicit method call prevents potential stray test-local references to the $config object
        // preventing the destructor from running the clean up (which without stray references would be
        // automagically triggered when `self::$phpcsFile` is reset, but we can't definitively rely on that).
        if (isset(self::$phpcsFile) === \true) {
            self::$phpcsFile->config->__destruct();
        }
        self::$tabWidth = 4;
        self::$phpcsFile = null;
    }
    /**
     * Test QA: verify that a test case file does not contain any duplicate test markers.
     *
     * When a test case file contains a lot of test cases, it is easy to overlook that a test marker name
     * is already in use.
     * A test wouldn't necessarily fail on this, but would not be testing what is intended to be tested as
     * it would be verifying token properties for the wrong token.
     *
     * This test safeguards against this.
     *
     * @coversNothing
     *
     * @return void
     */
    public function testTestMarkersAreUnique()
    {
        $this->assertTestMarkersAreUnique(self::$phpcsFile);
    }
    /**
     * Assertion to verify that a test case file does not contain any duplicate test markers.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file to validate.
     *
     * @return void
     */
    public static function assertTestMarkersAreUnique(File $phpcsFile)
    {
        $tokens = $phpcsFile->getTokens();
        // Collect all marker comments in the file.
        $seenComments = [];
        for ($i = 0; $i < $phpcsFile->numTokens; $i++) {
            if ($tokens[$i]['code'] !== \T_COMMENT) {
                continue;
            }
            if (\stripos($tokens[$i]['content'], '/* test') !== 0) {
                continue;
            }
            $seenComments[] = $tokens[$i]['content'];
        }
        self::assertSame(\array_unique($seenComments), $seenComments, 'Duplicate test markers found.');
    }
    /**
     * Get the token pointer for a target token based on a specific comment found on the line before.
     *
     * Note: the test delimiter comment MUST start with "/* test" to allow this function to
     * distinguish between comments used *in* a test and test delimiters.
     *
     * @param string                       $commentString The delimiter comment to look for.
     * @param int|string|array<int|string> $tokenType     The type of token(s) to look for.
     * @param string                       $tokenContent  Optional. The token content for the target token.
     *
     * @return int
     */
    public function getTargetToken($commentString, $tokenType, $tokenContent = null)
    {
        return self::getTargetTokenFromFile(self::$phpcsFile, $commentString, $tokenType, $tokenContent);
    }
    /**
     * Get the token pointer for a target token based on a specific comment found on the line before.
     *
     * Note: the test delimiter comment MUST start with "/* test" to allow this function to
     * distinguish between comments used *in* a test and test delimiters.
     *
     * @param \PHP_CodeSniffer\Files\File  $phpcsFile     The file to find the token in.
     * @param string                       $commentString The delimiter comment to look for.
     * @param int|string|array<int|string> $tokenType     The type of token(s) to look for.
     * @param string                       $tokenContent  Optional. The token content for the target token.
     *
     * @return int
     *
     * @throws \Exception When the test delimiter comment is not found.
     * @throws \Exception When the test target token is not found.
     */
    public static function getTargetTokenFromFile(File $phpcsFile, $commentString, $tokenType, $tokenContent = null)
    {
        $start = $phpcsFile->numTokens - 1;
        $comment = $phpcsFile->findPrevious(\T_COMMENT, $start, null, \false, $commentString);
        if ($comment === \false) {
            throw new Exception(\sprintf('Failed to find the test marker: %s in test case file %s', $commentString, $phpcsFile->getFilename()));
        }
        $tokens = $phpcsFile->getTokens();
        $end = $start + 1;
        // Limit the token finding to between this and the next delimiter comment.
        for ($i = $comment + 1; $i < $end; $i++) {
            if ($tokens[$i]['code'] !== \T_COMMENT) {
                continue;
            }
            if (\stripos($tokens[$i]['content'], '/* test') === 0) {
                $end = $i;
                break;
            }
        }
        $target = $phpcsFile->findNext($tokenType, $comment + 1, $end, \false, $tokenContent);
        if ($target === \false) {
            $msg = 'Failed to find test target token for comment string: ' . $commentString;
            if ($tokenContent !== null) {
                $msg .= ' with token content: ' . $tokenContent;
            }
            throw new Exception($msg);
        }
        return $target;
    }
    /**
     * Helper method to tell PHPUnit to expect a PHPCS RuntimeException in a PHPUnit cross-version
     * compatible manner.
     *
     * @param string $message The expected exception message.
     *
     * @return void
     */
    public function expectRunTimeException($message)
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);
    }
}
