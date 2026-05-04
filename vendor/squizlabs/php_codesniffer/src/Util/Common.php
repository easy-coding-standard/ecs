<?php

/**
 * Basic util functions.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Util;

use InvalidArgumentException;
use Phar;
class Common
{
    /**
     * An array of variable types for param/var we will check.
     *
     * @var array<string, string>
     */
    public const ALLOWED_TYPES = ['array' => 'array', 'boolean' => 'boolean', 'float' => 'float', 'integer' => 'integer', 'mixed' => 'mixed', 'object' => 'object', 'string' => 'string', 'resource' => 'resource', 'callable' => 'callable'];
    /**
     * An array of variable types for param/var we will check.
     *
     * @var array<string, string>
     *
     * @deprecated 4.0.0 Use the Common::ALLOWED_TYPES constant instead.
     */
    public static $allowedTypes = self::ALLOWED_TYPES;
    /**
     * Return TRUE if the path is a PHAR file.
     *
     * @param string $path The path to use.
     *
     * @return bool
     */
    public static function isPharFile(string $path)
    {
        if (strpos($path, 'phar://') === 0) {
            return \true;
        }
        return \false;
    }
    /**
     * Checks if a file is readable.
     *
     * Addresses PHP bug related to reading files from network drives on Windows.
     * e.g. when using WSL2.
     *
     * @param string $path The path to the file.
     *
     * @return boolean
     */
    public static function isReadable(string $path)
    {
        if (@is_readable($path) === \true) {
            return \true;
        }
        if (@file_exists($path) === \true && @is_file($path) === \true) {
            $f = @fopen($path, 'rb');
            if (fclose($f) === \true) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * CodeSniffer alternative for realpath.
     *
     * Allows for PHAR support.
     *
     * @param string $path The path to use.
     *
     * @return string|false
     */
    public static function realpath(string $path)
    {
        // Support the path replacement of ~ with the user's home directory.
        if (substr($path, 0, 2) === '~/') {
            $homeDir = getenv('HOME');
            if ($homeDir !== \false) {
                $path = $homeDir . substr($path, 1);
            }
        }
        // Check for process substitution.
        if (strpos($path, '/dev/fd') === 0) {
            return str_replace('/dev/fd', 'php://fd', $path);
        }
        // No extra work needed if this is not a phar file.
        if (self::isPharFile($path) === \false) {
            return realpath($path);
        }
        // Before trying to break down the file path,
        // check if it exists first because it will mostly not
        // change after running the below code.
        if (file_exists($path) === \true) {
            return $path;
        }
        $phar = Phar::running(\false);
        $extra = str_replace('phar://' . $phar, '', $path);
        $path = realpath($phar);
        if ($path === \false) {
            return \false;
        }
        $path = 'phar://' . $path . $extra;
        if (file_exists($path) === \true) {
            return $path;
        }
        return \false;
    }
    /**
     * Removes a base path from the front of a file path.
     *
     * @param string      $path     The path of the file.
     * @param string|null $basepath The base path to remove. This should not end
     *                              with a directory separator.
     *
     * @return string
     */
    public static function stripBasepath(string $path, ?string $basepath)
    {
        if (empty($basepath) === \true) {
            return $path;
        }
        $basepathLen = strlen($basepath);
        if ((string) substr($path, 0, $basepathLen) === $basepath) {
            $path = (string) substr($path, $basepathLen);
        }
        $path = ltrim($path, \DIRECTORY_SEPARATOR);
        if ($path === '') {
            $path = '.';
        }
        return $path;
    }
    /**
     * Detects the EOL character being used in a string.
     *
     * @param string $contents The contents to check.
     *
     * @return string
     */
    public static function detectLineEndings(string $contents)
    {
        if (preg_match("/\r\n?|\n/", $contents, $matches) !== 1) {
            // Assume there are no newlines.
            $eolChar = "\n";
        } else {
            $eolChar = $matches[0];
        }
        return $eolChar;
    }
    /**
     * Check if STDIN is a TTY.
     *
     * @return boolean
     */
    public static function isStdinATTY()
    {
        // The check is slow (especially calling `tty`) so we static
        // cache the result.
        static $isTTY = null;
        if ($isTTY !== null) {
            return $isTTY;
        }
        if (defined('STDIN') === \false) {
            return \false;
        }
        // If PHP has the POSIX extensions we will use them.
        if (function_exists('posix_isatty') === \true) {
            $isTTY = posix_isatty(\STDIN) === \true;
            return $isTTY;
        }
        // Next try is detecting whether we have `tty` installed and use that.
        if (\PHP_OS_FAMILY === 'Windows') {
            $devnull = 'NUL';
            $which = 'where';
        } else {
            $devnull = '/dev/null';
            $which = 'which';
        }
        $tty = trim(shell_exec("{$which} tty 2> {$devnull}"));
        if (empty($tty) === \false) {
            exec("tty -s 2> {$devnull}", $output, $returnValue);
            $isTTY = $returnValue === 0;
            return $isTTY;
        }
        // Finally we will use fstat.  The solution borrowed from
        // https://stackoverflow.com/questions/11327367/detect-if-a-php-script-is-being-run-interactively-or-not
        // This doesn't work on Mingw/Cygwin/... using Mintty but they
        // have `tty` installed.
        $type = ['S_IFMT' => 0170000, 'S_IFIFO' => 010000];
        $stat = fstat(\STDIN);
        $mode = $stat['mode'] & $type['S_IFMT'];
        $isTTY = $mode !== $type['S_IFIFO'];
        return $isTTY;
    }
    /**
     * Escape a path to a system command.
     *
     * @param string $cmd The path to the system command.
     *
     * @return string
     */
    public static function escapeshellcmd(string $cmd)
    {
        $cmd = escapeshellcmd($cmd);
        if (\PHP_OS_FAMILY === 'Windows') {
            // Spaces are not escaped by escapeshellcmd on Windows, but need to be
            // for the command to be able to execute.
            $cmd = preg_replace('`(?<!^) `', '^ ', $cmd);
        }
        return $cmd;
    }
    /**
     * Prepares token content for output to screen.
     *
     * Replaces invisible characters so they are visible. On non-Windows
     * operating systems it will also colour the invisible characters.
     *
     * @param string   $content The content to prepare.
     * @param string[] $exclude A list of characters to leave invisible.
     *                          Can contain \r, \n, \t and a space.
     *
     * @return string
     */
    public static function prepareForOutput(string $content, array $exclude = [])
    {
        if (\PHP_OS_FAMILY === 'Windows') {
            if (in_array("\r", $exclude, \true) === \false) {
                $content = str_replace("\r", '\r', $content);
            }
            if (in_array("\n", $exclude, \true) === \false) {
                $content = str_replace("\n", '\n', $content);
            }
            if (in_array("\t", $exclude, \true) === \false) {
                $content = str_replace("\t", '\t', $content);
            }
        } else {
            if (in_array("\r", $exclude, \true) === \false) {
                $content = str_replace("\r", "\x1b[30;1m\\r\x1b[0m", $content);
            }
            if (in_array("\n", $exclude, \true) === \false) {
                $content = str_replace("\n", "\x1b[30;1m\\n\x1b[0m", $content);
            }
            if (in_array("\t", $exclude, \true) === \false) {
                $content = str_replace("\t", "\x1b[30;1m\\t\x1b[0m", $content);
            }
            if (in_array(' ', $exclude, \true) === \false) {
                $content = str_replace(' ', "\x1b[30;1m·\x1b[0m", $content);
            }
        }
        return $content;
    }
    /**
     * Strip colors from a text for output to screen.
     *
     * @param string $text The text to process.
     *
     * @return string
     */
    public static function stripColors(string $text)
    {
        return preg_replace('`\033\[[0-9;]+m`', '', $text);
    }
    /**
     * Returns true if the specified text string is in the camel caps format.
     *
     * @param string  $name             The string to verify.
     * @param boolean $classFormat      If true, check to see if the string is in the
     *                                  class format. Class format strings must start
     *                                  with a capital letter and contain no
     *                                  underscores.
     * @param boolean $visibilityPublic If true, the first character in the string
     *                                  must be an a-z character. If false, the
     *                                  character must be an underscore. This
     *                                  argument is only applicable if $classFormat
     *                                  is false.
     * @param boolean $strict           If true, the string must not have two capital
     *                                  letters next to each other. If false, a
     *                                  relaxed camel caps policy is used to allow
     *                                  for acronyms.
     *
     * @return boolean
     */
    public static function isCamelCaps(string $name, bool $classFormat = \false, bool $visibilityPublic = \true, bool $strict = \true)
    {
        // Check the first character first.
        if ($classFormat === \false) {
            $legalFirstChar = '';
            if ($visibilityPublic === \false) {
                $legalFirstChar = '[_]';
            }
            if ($strict === \false) {
                // Can either start with a lowercase letter, or multiple uppercase
                // in a row, representing an acronym.
                $legalFirstChar .= '([A-Z]{2,}|[a-z])';
            } else {
                $legalFirstChar .= '[a-z]';
            }
        } else {
            $legalFirstChar = '[A-Z]';
        }
        if (preg_match("/^{$legalFirstChar}/", $name) === 0) {
            return \false;
        }
        // Check that the name only contains legal characters.
        $legalChars = 'a-zA-Z0-9';
        if (preg_match("|[^{$legalChars}]|", (string) substr($name, 1)) > 0) {
            return \false;
        }
        if ($strict === \true) {
            // Check that there are not two capital letters next to each other.
            $length = strlen($name);
            $lastCharWasCaps = $classFormat;
            for ($i = 1; $i < $length; $i++) {
                $ascii = ord($name[$i]);
                if ($ascii >= 48 && $ascii <= 57) {
                    // The character is a number, so it can't be a capital.
                    $isCaps = \false;
                } else if (strtoupper($name[$i]) === $name[$i]) {
                    $isCaps = \true;
                } else {
                    $isCaps = \false;
                }
                if ($isCaps === \true && $lastCharWasCaps === \true) {
                    return \false;
                }
                $lastCharWasCaps = $isCaps;
            }
        }
        return \true;
    }
    /**
     * Returns true if the specified text string is in the underscore caps format.
     *
     * @param string $name The text string to verify.
     *
     * @return boolean
     */
    public static function isUnderscoreName(string $name)
    {
        // If there is whitespace in the name, it can't be valid.
        if (strpos($name, ' ') !== \false) {
            return \false;
        }
        $validName = \true;
        $nameBits = explode('_', $name);
        if (preg_match('|^[A-Z]|', $name) === 0) {
            // Name does not begin with a capital letter.
            $validName = \false;
        } else {
            foreach ($nameBits as $bit) {
                if ($bit === '') {
                    continue;
                }
                if ($bit[0] !== strtoupper($bit[0])) {
                    $validName = \false;
                    break;
                }
            }
        }
        return $validName;
    }
    /**
     * Returns a valid variable type for param/var tags.
     *
     * If type is not one of the standard types, it must be a custom type.
     * Returns the correct type name suggestion if type name is invalid.
     *
     * @param string $varType The variable type to process.
     *
     * @return string
     */
    public static function suggestType(string $varType)
    {
        if ($varType === '') {
            return '';
        }
        if (isset(self::ALLOWED_TYPES[$varType]) === \true) {
            return $varType;
        } else {
            $lowerVarType = strtolower($varType);
            switch ($lowerVarType) {
                case 'bool':
                case 'boolean':
                    return 'boolean';
                case 'double':
                case 'real':
                case 'float':
                    return 'float';
                case 'int':
                case 'integer':
                    return 'integer';
                case 'array()':
                case 'array':
                    return 'array';
            }
            if (strpos($lowerVarType, 'array(') !== \false) {
                // Valid array declaration:
                // array, array(type), array(type1 => type2).
                $matches = [];
                $pattern = '/^array\(\s*([^\s^=^>]*)(\s*=>\s*(.*))?\s*\)/i';
                if (preg_match($pattern, $varType, $matches) !== 0) {
                    $type1 = '';
                    if (isset($matches[1]) === \true) {
                        $type1 = $matches[1];
                    }
                    $type2 = '';
                    if (isset($matches[3]) === \true) {
                        $type2 = $matches[3];
                    }
                    $type1 = self::suggestType($type1);
                    $type2 = self::suggestType($type2);
                    if ($type2 !== '') {
                        $type2 = ' => ' . $type2;
                    }
                    return "array({$type1}{$type2})";
                } else {
                    return 'array';
                }
            } elseif (isset(self::ALLOWED_TYPES[$lowerVarType]) === \true) {
                // A valid type, but not lower cased.
                return $lowerVarType;
            } else {
                // Must be a custom type name.
                return $varType;
            }
        }
    }
    /**
     * Given a sniff class name, returns the code for the sniff.
     *
     * @param string $sniffClass The fully qualified sniff class name.
     *
     * @return string
     *
     * @throws \InvalidArgumentException When $sniffClass is not a non-empty string.
     * @throws \InvalidArgumentException When $sniffClass is not a valid FQN for a sniff(test) class.
     */
    public static function getSniffCode($sniffClass)
    {
        if (is_string($sniffClass) === \false || $sniffClass === '') {
            throw new InvalidArgumentException('The $sniffClass parameter must be a non-empty string');
        }
        $parts = explode('\\', $sniffClass);
        $partsCount = count($parts);
        if ($partsCount < 4 || $parts[$partsCount - 3] !== 'Sniffs' && $parts[$partsCount - 3] !== 'Tests' || $parts[$partsCount - 2] === 'Sniffs') {
            throw new InvalidArgumentException('The $sniffClass parameter was not passed a fully qualified sniff(test) class name. Received: ' . $sniffClass);
        }
        $sniff = $parts[$partsCount - 1];
        if ($sniff !== 'Sniff' && substr($sniff, -5) === 'Sniff') {
            // Sniff class name.
            $sniff = (string) substr($sniff, 0, -5);
        } elseif ($sniff !== 'UnitTest' && substr($sniff, -8) === 'UnitTest') {
            // Unit test class name.
            $sniff = (string) substr($sniff, 0, -8);
        } else {
            throw new InvalidArgumentException('The $sniffClass parameter was not passed a fully qualified sniff(test) class name. Received: ' . $sniffClass);
        }
        $standard = $parts[$partsCount - 4];
        $category = $parts[$partsCount - 2];
        return $standard . '.' . $category . '.' . $sniff;
    }
    /**
     * Removes project-specific information from a sniff class name.
     *
     * @param string $sniffClass The fully qualified sniff class name.
     *
     * @return string
     */
    public static function cleanSniffClass(string $sniffClass)
    {
        $newName = strtolower($sniffClass);
        $sniffPos = strrpos($newName, '\sniffs\\');
        if ($sniffPos === \false) {
            // Nothing we can do as it isn't in a known format.
            return $newName;
        }
        $end = strlen($newName) - $sniffPos + 1;
        $start = strrpos($newName, '\\', $end * -1);
        if ($start === \false) {
            // Nothing needs to be cleaned.
            return $newName;
        }
        $newName = (string) substr($newName, $start + 1);
        return $newName;
    }
}
