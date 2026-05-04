<?php

/**
 * Tests that the correct Subversion properties are set.
 *
 * @author    Jack Bates <ms419@freezone.co.uk>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Standards\Generic\Sniffs\VersionControl;

use PHP_CodeSniffer\Exceptions\RuntimeException;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
class SubversionPropertiesSniff implements Sniff
{
    /**
     * The Subversion properties that should be set.
     *
     * Key of array is the SVN property and the value is the
     * exact value the property should have or NULL if the
     * property should just be set but the value is not fixed.
     *
     * @var array<string, string>
     */
    protected const REQUIRED_PROPERTIES = ['svn:keywords' => 'Author Id Revision', 'svn:eol-style' => 'native'];
    /**
     * The Subversion properties that should be set.
     *
     * @var array<string, string>
     *
     * @deprecated 4.0.0 Use the SubversionPropertiesSniff::REQUIRED_PROPERTIES constant instead.
     */
    protected $properties = self::REQUIRED_PROPERTIES;
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int|string>
     */
    public function register()
    {
        return [\T_OPEN_TAG];
    }
    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return int
     */
    public function process(File $phpcsFile, int $stackPtr)
    {
        $path = $phpcsFile->getFilename();
        $properties = $this->getProperties($path);
        if ($properties === null) {
            // Not under version control.
            return $phpcsFile->numTokens;
        }
        $allProperties = $properties + static::REQUIRED_PROPERTIES;
        foreach ($allProperties as $key => $value) {
            if (isset($properties[$key]) === \true && isset(static::REQUIRED_PROPERTIES[$key]) === \false) {
                $error = 'Unexpected Subversion property "%s" = "%s"';
                $data = [$key, $properties[$key]];
                $phpcsFile->addError($error, $stackPtr, 'Unexpected', $data);
                continue;
            }
            if (isset($properties[$key]) === \false && isset(static::REQUIRED_PROPERTIES[$key]) === \true) {
                $error = 'Missing Subversion property "%s" = "%s"';
                $data = [$key, static::REQUIRED_PROPERTIES[$key]];
                $phpcsFile->addError($error, $stackPtr, 'Missing', $data);
                continue;
            }
            if ($properties[$key] !== null && $properties[$key] !== static::REQUIRED_PROPERTIES[$key]) {
                $error = 'Subversion property "%s" = "%s" does not match "%s"';
                $data = [$key, $properties[$key], static::REQUIRED_PROPERTIES[$key]];
                $phpcsFile->addError($error, $stackPtr, 'NoMatch', $data);
            }
        }
        // Ignore the rest of the file.
        return $phpcsFile->numTokens;
    }
    /**
     * Returns the Subversion properties which are actually set on a path.
     *
     * Returns NULL if the file is not under version control.
     *
     * @param string $path The path to return Subversion properties on.
     *
     * @return array|null
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException If Subversion properties file could
     *                                                      not be opened.
     */
    protected function getProperties(string $path)
    {
        $properties = [];
        $paths = [];
        $paths[] = dirname($path) . '/.svn/props/' . basename($path) . '.svn-work';
        $paths[] = dirname($path) . '/.svn/prop-base/' . basename($path) . '.svn-base';
        $foundPath = \false;
        foreach ($paths as $path) {
            if (file_exists($path) === \true) {
                $foundPath = \true;
                $handle = fopen($path, 'r');
                if ($handle === \false) {
                    $error = 'Error opening file; could not get Subversion properties';
                    throw new RuntimeException($error);
                }
                while (feof($handle) === \false) {
                    // Read a key length line. Might be END, though.
                    $buffer = trim(fgets($handle));
                    // Check for the end of the hash.
                    if ($buffer === 'END') {
                        break;
                    }
                    // Now read that much into a buffer.
                    $key = fread($handle, (string) substr($buffer, 2));
                    // Suck up extra newline after key data.
                    fgetc($handle);
                    // Read a value length line.
                    $buffer = trim(fgets($handle));
                    // Now read that much into a buffer.
                    $length = (string) substr($buffer, 2);
                    if ($length === '0') {
                        // Length of value is ZERO characters, so
                        // value is actually empty.
                        $value = '';
                    } else {
                        $value = fread($handle, $length);
                    }
                    // Suck up extra newline after value data.
                    fgetc($handle);
                    $properties[$key] = $value;
                }
                fclose($handle);
            }
        }
        if ($foundPath === \false) {
            return null;
        }
        return $properties;
    }
}
