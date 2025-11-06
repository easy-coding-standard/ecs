<?php

/**
 * Functions for helping process standards.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Util;

use DirectoryIterator;
use PHP_CodeSniffer\Config;
class Standards
{
    /**
     * Get a list of paths where standards are installed.
     *
     * Unresolvable relative paths will be excluded from the results.
     *
     * @return array
     */
    public static function getInstalledStandardPaths()
    {
        $ds = \DIRECTORY_SEPARATOR;
        $installedPaths = [\dirname(__DIR__, 2) . $ds . 'src' . $ds . 'Standards'];
        $configPaths = Config::getConfigData('installed_paths');
        if ($configPaths !== null) {
            $installedPaths = \array_merge($installedPaths, \explode(',', $configPaths));
        }
        $resolvedInstalledPaths = [];
        foreach ($installedPaths as $installedPath) {
            if (\substr($installedPath, 0, 1) === '.') {
                $installedPath = \PHP_CodeSniffer\Util\Common::realpath(__DIR__ . $ds . '..' . $ds . '..' . $ds . $installedPath);
                if ($installedPath === \false) {
                    continue;
                }
            }
            $resolvedInstalledPaths[] = $installedPath;
        }
        return $resolvedInstalledPaths;
    }
    /**
     * Get the details of all coding standards installed.
     *
     * Coding standards are directories located in the
     * CodeSniffer/Standards directory. Valid coding standards
     * include a Sniffs subdirectory.
     *
     * The details returned for each standard are:
     * - path:      the path to the coding standard's main directory
     * - name:      the name of the coding standard, as sourced from the ruleset.xml file
     * - namespace: the namespace used by the coding standard, as sourced from the ruleset.xml file
     *
     * If you only need the paths to the installed standards,
     * use getInstalledStandardPaths() instead as it performs less work to
     * retrieve coding standard names.
     *
     * @param boolean $includeGeneric If true, the special "Generic"
     *                                coding standard will be included
     *                                if installed.
     * @param string  $standardsDir   A specific directory to look for standards
     *                                in. If not specified, PHP_CodeSniffer will
     *                                look in its default locations.
     *
     * @return array
     * @see    getInstalledStandardPaths()
     */
    public static function getInstalledStandardDetails(bool $includeGeneric = \false, string $standardsDir = '')
    {
        $rulesets = [];
        if ($standardsDir === '') {
            $installedPaths = self::getInstalledStandardPaths();
        } else {
            $installedPaths = [$standardsDir];
        }
        foreach ($installedPaths as $standardsDir) {
            // Check if the installed dir is actually a standard itself.
            $csFile = $standardsDir . '/ruleset.xml';
            if (\is_file($csFile) === \true) {
                $rulesets[] = $csFile;
                continue;
            }
            if (\is_dir($standardsDir) === \false) {
                continue;
            }
            $di = new DirectoryIterator($standardsDir);
            foreach ($di as $file) {
                if ($file->isDir() === \true && $file->isDot() === \false) {
                    $filename = $file->getFilename();
                    // Ignore the special "Generic" standard.
                    if ($includeGeneric === \false && $filename === 'Generic') {
                        continue;
                    }
                    // Valid coding standard dirs include a ruleset.
                    $csFile = $file->getPathname() . '/ruleset.xml';
                    if (\is_file($csFile) === \true) {
                        $rulesets[] = $csFile;
                    }
                }
            }
        }
        $installedStandards = [];
        foreach ($rulesets as $rulesetPath) {
            $ruleset = @\simplexml_load_string(\file_get_contents($rulesetPath));
            if ($ruleset === \false) {
                continue;
            }
            $standardName = (string) $ruleset['name'];
            $dirname = \basename(\dirname($rulesetPath));
            if (isset($ruleset['namespace']) === \true) {
                $namespace = (string) $ruleset['namespace'];
            } else {
                $namespace = $dirname;
            }
            $installedStandards[$dirname] = ['path' => \dirname($rulesetPath), 'name' => $standardName, 'namespace' => $namespace];
        }
        return $installedStandards;
    }
    /**
     * Get a list of all coding standards installed.
     *
     * Coding standards are directories located in the
     * CodeSniffer/Standards directory. Valid coding standards
     * include a Sniffs subdirectory.
     *
     * @param boolean $includeGeneric If true, the special "Generic"
     *                                coding standard will be included
     *                                if installed.
     * @param string  $standardsDir   A specific directory to look for standards
     *                                in. If not specified, PHP_CodeSniffer will
     *                                look in its default locations.
     *
     * @return array
     * @see    isInstalledStandard()
     */
    public static function getInstalledStandards(bool $includeGeneric = \false, string $standardsDir = '')
    {
        $installedStandards = [];
        if ($standardsDir === '') {
            $installedPaths = self::getInstalledStandardPaths();
        } else {
            $installedPaths = [$standardsDir];
        }
        foreach ($installedPaths as $standardsDir) {
            // Check if the installed dir is actually a standard itself.
            $csFile = $standardsDir . '/ruleset.xml';
            if (\is_file($csFile) === \true) {
                $basename = \basename($standardsDir);
                $installedStandards[$basename] = $basename;
                continue;
            }
            if (\is_dir($standardsDir) === \false) {
                // Doesn't exist.
                continue;
            }
            $di = new DirectoryIterator($standardsDir);
            $standardsInDir = [];
            foreach ($di as $file) {
                if ($file->isDir() === \true && $file->isDot() === \false) {
                    $filename = $file->getFilename();
                    // Ignore the special "Generic" standard.
                    if ($includeGeneric === \false && $filename === 'Generic') {
                        continue;
                    }
                    // Valid coding standard dirs include a ruleset.
                    $csFile = $file->getPathname() . '/ruleset.xml';
                    if (\is_file($csFile) === \true) {
                        $standardsInDir[$filename] = $filename;
                    }
                }
            }
            \natsort($standardsInDir);
            $installedStandards += $standardsInDir;
        }
        return $installedStandards;
    }
    /**
     * Determine if a standard is installed.
     *
     * Coding standards are directories located in the
     * CodeSniffer/Standards directory. Valid coding standards
     * include a ruleset.xml file.
     *
     * @param string $standard The name of the coding standard.
     *
     * @return boolean
     * @see    getInstalledStandards()
     */
    public static function isInstalledStandard(string $standard)
    {
        $path = self::getInstalledStandardPath($standard);
        if ($path !== null && \strpos($path, 'ruleset.xml') !== \false) {
            return \true;
        } else {
            // This could be a custom standard, installed outside our
            // standards directory.
            $standard = \PHP_CodeSniffer\Util\Common::realpath($standard);
            if ($standard === \false) {
                return \false;
            }
            // Might be an actual ruleset file itUtil.
            // If it has an XML extension, let's at least try it.
            if (\is_file($standard) === \true && (\substr(\strtolower($standard), -4) === '.xml' || \substr(\strtolower($standard), -9) === '.xml.dist')) {
                return \true;
            }
            // If it is a directory with a ruleset.xml file in it,
            // it is a standard.
            $ruleset = \rtrim($standard, ' /\\') . \DIRECTORY_SEPARATOR . 'ruleset.xml';
            if (\is_file($ruleset) === \true) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Return the path of an installed coding standard.
     *
     * Coding standards are directories located in the
     * CodeSniffer/Standards directory. Valid coding standards
     * include a ruleset.xml file.
     *
     * @param string $standard The name of the coding standard.
     *
     * @return string|null
     */
    public static function getInstalledStandardPath(string $standard)
    {
        if (\strpos($standard, '.') !== \false) {
            return null;
        }
        $installedPaths = self::getInstalledStandardPaths();
        foreach ($installedPaths as $installedPath) {
            $standardPath = $installedPath . \DIRECTORY_SEPARATOR . $standard;
            if (\file_exists($standardPath) === \false) {
                if (\basename($installedPath) !== $standard) {
                    continue;
                }
                $standardPath = $installedPath;
            }
            $path = \PHP_CodeSniffer\Util\Common::realpath($standardPath . \DIRECTORY_SEPARATOR . 'ruleset.xml');
            if ($path !== \false && \is_file($path) === \true) {
                return $path;
            } elseif (\PHP_CodeSniffer\Util\Common::isPharFile($standardPath) === \true) {
                $path = \PHP_CodeSniffer\Util\Common::realpath($standardPath);
                if ($path !== \false) {
                    return $path;
                }
            }
        }
        return null;
    }
    /**
     * Prepares a list of installed coding standards for display.
     *
     * @return string
     */
    public static function prepareInstalledStandardsForDisplay()
    {
        $installedStandards = self::getInstalledStandards();
        $numStandards = \count($installedStandards);
        $output = '';
        if ($numStandards === 0) {
            $output .= 'No coding standards are installed.';
        } else {
            $lastStandard = \array_pop($installedStandards);
            if ($numStandards === 1) {
                $output .= "The only coding standard installed is {$lastStandard}";
            } else {
                $standardList = \implode(', ', $installedStandards);
                $standardList .= ' and ' . $lastStandard;
                $output .= 'The installed coding standards are ' . $standardList;
            }
        }
        return $output;
    }
    /**
     * Prints out a list of installed coding standards.
     *
     * @deprecated 4.0.0 Use `echo Standards::prepareInstalledStandardsForDisplay()` instead.
     *
     * @return void
     */
    public static function printInstalledStandards()
    {
        echo self::prepareInstalledStandardsForDisplay(), \PHP_EOL;
    }
}
