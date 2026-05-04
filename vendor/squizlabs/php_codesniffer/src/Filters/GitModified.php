<?php

/**
 * A filter to only include files that have been modified or added in a Git repository.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Filters;

use PHP_CodeSniffer\Util\Common;
class GitModified extends \PHP_CodeSniffer\Filters\ExactMatch
{
    /**
     * Get a list of file paths to exclude.
     *
     * @since 3.9.0 Replaces the `getBlacklist()` method, which was removed in PHPCS 4.0.0.
     *
     * @return array
     */
    protected function getDisallowedFiles()
    {
        return [];
    }
    /**
     * Get a list of file paths to include.
     *
     * @since 3.9.0 Replaces the `getWhitelist()` method, which was removed in PHPCS 4.0.0.
     *
     * @return array
     */
    protected function getAllowedFiles()
    {
        $modified = [];
        $cmd = 'git ls-files -o -m --exclude-standard -- ' . escapeshellarg($this->basedir);
        $output = $this->exec($cmd);
        $basedir = $this->basedir;
        if (is_dir($basedir) === \false) {
            $basedir = dirname($basedir);
        }
        foreach ($output as $path) {
            $path = Common::realpath($path);
            if ($path === \false) {
                continue;
            }
            do {
                $modified[$path] = \true;
                $path = dirname($path);
            } while ($path !== $basedir);
        }
        return $modified;
    }
    /**
     * Execute an external command.
     *
     * {@internal This method is only needed to allow for mocking the return value
     * to test the class logic.}
     *
     * @param string $cmd Command.
     *
     * @return array
     */
    protected function exec(string $cmd)
    {
        $output = [];
        $lastLine = exec($cmd, $output);
        if ($lastLine === \false) {
            return [];
        }
        return $output;
    }
}
