<?php

/**
 * Timing functions for the run.
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
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Util;

use PHP_CodeSniffer\Util\Writers\StatusWriter;
final class Timing
{
    /**
     * Number of milliseconds in a minute.
     *
     * @var int
     */
    private const MINUTE_IN_MS = 60000;
    /**
     * Number of milliseconds in a second.
     *
     * @var int
     */
    private const SECOND_IN_MS = 1000;
    /**
     * The start time of the run in seconds.
     *
     * @var float
     */
    private static $startTime;
    /**
     * Used to make sure we only print the run time once per run.
     *
     * @var boolean
     */
    private static $printed = \false;
    /**
     * Start recording time for the run.
     *
     * @return void
     */
    public static function startTiming()
    {
        self::$startTime = microtime(\true);
    }
    /**
     * Get the duration of the run up to "now".
     *
     * @return float Duration in milliseconds.
     */
    public static function getDuration()
    {
        if (self::$startTime === null) {
            // Timing was never started.
            return 0;
        }
        return (microtime(\true) - self::$startTime) * 1000;
    }
    /**
     * Get the duration since a given start time up to "now".
     *
     * @param float $startTime Start time in microseconds.
     *
     * @return float Duration in milliseconds.
     */
    public static function getDurationSince(float $startTime)
    {
        return (microtime(\true) - $startTime) * 1000;
    }
    /**
     * Convert a duration in milliseconds to a human readable duration string.
     *
     * @param float $duration Duration in milliseconds.
     *
     * @return string
     */
    public static function getHumanReadableDuration(float $duration)
    {
        $timeString = '';
        if ($duration >= self::MINUTE_IN_MS) {
            $mins = floor($duration / self::MINUTE_IN_MS);
            $secs = round(fmod($duration, self::MINUTE_IN_MS) / self::SECOND_IN_MS, 2);
            $timeString = $mins . ' mins';
            if ($secs >= 0.01) {
                $timeString .= ", {$secs} secs";
            }
        } elseif ($duration >= self::SECOND_IN_MS) {
            $timeString = round($duration / self::SECOND_IN_MS, 2) . ' secs';
        } else {
            $timeString = round($duration) . 'ms';
        }
        return $timeString;
    }
    /**
     * Print information about the run.
     *
     * @param boolean $force If TRUE, prints the output even if it has
     *                       already been printed during the run.
     *
     * @return void
     */
    public static function printRunTime(bool $force = \false)
    {
        if ($force === \false && self::$printed === \true) {
            // A double call.
            return;
        }
        if (self::$startTime === null) {
            // Timing was never started.
            return;
        }
        $duration = self::getDuration();
        $duration = self::getHumanReadableDuration($duration);
        $mem = round(memory_get_peak_usage(\true) / (1024 * 1024), 2) . 'MB';
        StatusWriter::write("Time: {$duration}; Memory: {$mem}");
        self::$printed = \true;
    }
}
