<?php

/**
 * Status Writer to send output to STDERR.
 *
 * ---------------------------------------------------------------------------------------------
 * This class is intended for internal use only and is not part of the public API.
 * This also means that it has no promise of backward compatibility. Use at your own risk.
 * ---------------------------------------------------------------------------------------------
 *
 * @internal
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Juliette Reinders Folmer <phpcs_nospam@adviesenzo.nl>
 * @copyright 2025 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Util\Writers;

final class StatusWriter
{
    /**
     * The stream to write to.
     *
     * @var resource
     */
    private static $stream = \STDERR;
    /**
     * If TRUE, requests to print a status message will be ignored.
     *
     * @var boolean
     */
    private static $paused = \false;
    /**
     * Number of open pause requests.
     *
     * If the writer is paused from different places, we only want to resume when all those places
     * have given the okay for it. Let's call it "pause nesting".
     *
     * @var integer
     */
    private static $pauseCount = 0;
    /**
     * Prints a status message to STDERR.
     *
     * If status messages have been paused, the message will be not be output.
     * Use forceWrite() to forcibly print a message in this case.
     *
     * @param string $message  The message to print.
     * @param int    $indent   How many levels to indent the message.
     *                         Tabs are used to indent status
     *                         messages.
     * @param int    $newlines Number of new lines to add to the messages.
     *                         Defaults to 1. Set to 0 to suppress adding a new line.
     *
     * @return void
     */
    public static function write(string $message, int $indent = 0, int $newlines = 1)
    {
        if (self::$paused === \true) {
            return;
        }
        self::forceWrite($message, $indent, $newlines);
    }
    /**
     * Prints a status message to STDERR, even if status messages have been paused.
     *
     * @param string $message  The message to print.
     * @param int    $indent   How many levels to indent the message.
     *                         Tabs are used to indent status
     *                         messages.
     * @param int    $newlines Number of new lines to add to the messages.
     *                         Defaults to 1. Set to 0 to suppress adding a new line.
     *
     * @return void
     */
    public static function forceWrite(string $message, int $indent = 0, int $newlines = 1)
    {
        if ($indent > 0) {
            $message = \str_repeat("\t", $indent) . $message;
        }
        if ($newlines > 0) {
            $message .= \str_repeat(\PHP_EOL, $newlines);
        }
        \fwrite(self::$stream, $message);
    }
    /**
     * Prints a new line to STDERR.
     *
     * @param int $nr Number of new lines to print.
     *                Defaults to 1.
     *
     * @return void
     */
    public static function writeNewline(int $nr = 1)
    {
        self::write('', 0, $nr);
    }
    /**
     * Prints a new line to STDERR, even if status messages have been paused.
     *
     * @param int $nr Number of new lines to print.
     *                Defaults to 1.
     *
     * @return void
     */
    public static function forceWriteNewline(int $nr = 1)
    {
        self::forceWrite('', 0, $nr);
    }
    /**
     * Pauses the printing of status messages.
     *
     * @return void
     */
    public static function pause()
    {
        self::$paused = \true;
        ++self::$pauseCount;
    }
    /**
     * Resumes the printing of status messages.
     *
     * @return void
     */
    public static function resume()
    {
        if (self::$pauseCount > 0) {
            --self::$pauseCount;
        }
        if (self::$pauseCount === 0) {
            self::$paused = \false;
        }
    }
    /**
     * Check whether the StatusWriter is paused.
     *
     * @return bool
     */
    public static function isPaused()
    {
        return self::$paused;
    }
}
