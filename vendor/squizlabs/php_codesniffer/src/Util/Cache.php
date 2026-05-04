<?php

/**
 * Function for caching between runs.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Util;

use FilesystemIterator;
use PHP_CodeSniffer\Autoload;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Ruleset;
use PHP_CodeSniffer\Util\Writers\StatusWriter;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
class Cache
{
    /**
     * The filesystem location of the cache file.
     *
     * @var string
     */
    private static $path = '';
    /**
     * The cached data.
     *
     * @var array<string, mixed>
     */
    private static $cache = [];
    /**
     * Loads existing cache data for the run, if any.
     *
     * @param \PHP_CodeSniffer\Ruleset $ruleset The ruleset used for the run.
     * @param \PHP_CodeSniffer\Config  $config  The config data for the run.
     *
     * @return void
     */
    public static function load(Ruleset $ruleset, Config $config)
    {
        // Look at every loaded sniff class so far and use their file contents
        // to generate a hash for the code used during the run.
        // At this point, the loaded class list contains the core PHPCS code
        // and all sniffs that have been loaded as part of the run.
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            StatusWriter::writeNewline();
            StatusWriter::write('Generating loaded file list for code hash', 1);
        }
        $codeHashFiles = [];
        $classes = array_keys(Autoload::getLoadedClasses());
        sort($classes);
        $installDir = dirname(__DIR__);
        $installDirLen = strlen($installDir);
        $standardDir = $installDir . \DIRECTORY_SEPARATOR . 'Standards';
        $standardDirLen = strlen($standardDir);
        foreach ($classes as $file) {
            if (substr($file, 0, $standardDirLen) !== $standardDir) {
                if ((string) substr($file, 0, $installDirLen) === $installDir) {
                    // We are only interested in sniffs here.
                    continue;
                }
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    StatusWriter::write("=> external file: {$file}", 2);
                }
            } elseif (\PHP_CODESNIFFER_VERBOSITY > 1) {
                StatusWriter::write("=> internal sniff: {$file}", 2);
            }
            $codeHashFiles[] = $file;
        }
        // Add the content of the used rulesets to the hash so that sniff setting
        // changes in the ruleset invalidate the cache.
        $rulesets = $ruleset->paths;
        sort($rulesets);
        foreach ($rulesets as $file) {
            if (substr($file, 0, $standardDirLen) !== $standardDir) {
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    StatusWriter::write("=> external ruleset: {$file}", 2);
                }
            } elseif (\PHP_CODESNIFFER_VERBOSITY > 1) {
                StatusWriter::write("=> internal ruleset: {$file}", 2);
            }
            $codeHashFiles[] = $file;
        }
        // Go through the core PHPCS code and add those files to the file
        // hash. This ensures that core PHPCS changes will also invalidate the cache.
        // Note that we ignore sniffs here, and any files that don't affect
        // the outcome of the run.
        $di = new RecursiveDirectoryIterator($installDir, FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS);
        $filter = new RecursiveCallbackFilterIterator($di, static function ($file, $key, $iterator) {
            // Skip non-php files.
            $filename = $file->getFilename();
            if ($file->isFile() === \true && substr($filename, -4) !== '.php') {
                return \false;
            }
            $filePath = \PHP_CodeSniffer\Util\Common::realpath($key);
            if ($filePath === \false) {
                return \false;
            }
            if ($iterator->hasChildren() === \true && ($filename === 'Standards' || $filename === 'Exceptions' || $filename === 'Reports' || $filename === 'Generators')) {
                return \false;
            }
            return \true;
        });
        $iterator = new RecursiveIteratorIterator($filter);
        foreach ($iterator as $file) {
            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                StatusWriter::write("=> core file: {$file}", 2);
            }
            $codeHashFiles[] = $file->getPathname();
        }
        $codeHash = '';
        sort($codeHashFiles);
        foreach ($codeHashFiles as $file) {
            $codeHash .= md5_file($file);
        }
        $codeHash = md5($codeHash);
        // Along with the code hash, use various settings that can affect
        // the results of a run to create a new hash. This hash will be used
        // in the cache file name.
        $rulesetHash = md5(var_export($ruleset->ignorePatterns, \true) . var_export($ruleset->includePatterns, \true));
        $phpExtensionsHash = md5(var_export(get_loaded_extensions(), \true));
        $configData = ['phpVersion' => \PHP_VERSION_ID, 'phpExtensions' => $phpExtensionsHash, 'tabWidth' => $config->tabWidth, 'encoding' => $config->encoding, 'recordErrors' => $config->recordErrors, 'annotations' => $config->annotations, 'configData' => Config::getAllConfigData(), 'codeHash' => $codeHash, 'rulesetHash' => $rulesetHash];
        $configString = var_export($configData, \true);
        $cacheHash = substr(sha1($configString), 0, 12);
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            StatusWriter::write('Generating cache key data', 1);
            foreach ($configData as $key => $value) {
                if (is_array($value) === \true) {
                    StatusWriter::write("=> {$key}:", 2);
                    foreach ($value as $subKey => $subValue) {
                        StatusWriter::write("=> {$subKey}: {$subValue}", 3);
                    }
                    continue;
                }
                if ($value === \true || $value === \false) {
                    $value = (int) $value;
                }
                StatusWriter::write("=> {$key}: {$value}", 2);
            }
            StatusWriter::write("=> cacheHash: {$cacheHash}", 2);
        }
        if ($config->cacheFile !== null) {
            $cacheFile = $config->cacheFile;
        } else {
            // Determine the common paths for all files being checked.
            // We can use this to locate an existing cache file, or to
            // determine where to create a new one.
            if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                StatusWriter::write('Checking possible cache file paths', 1);
            }
            $paths = [];
            foreach ($config->files as $file) {
                $file = \PHP_CodeSniffer\Util\Common::realpath($file);
                while ($file !== \DIRECTORY_SEPARATOR) {
                    if (isset($paths[$file]) === \false) {
                        $paths[$file] = 1;
                    } else {
                        $paths[$file]++;
                    }
                    $lastFile = $file;
                    $file = dirname($file);
                    if ($file === $lastFile) {
                        // Just in case something went wrong,
                        // we don't want to end up in an infinite loop.
                        break;
                    }
                }
            }
            ksort($paths);
            $paths = array_reverse($paths);
            $numFiles = count($config->files);
            $cacheFile = null;
            $cacheDir = getenv('XDG_CACHE_HOME');
            if ($cacheDir === \false || is_dir($cacheDir) === \false) {
                $cacheDir = sys_get_temp_dir();
            }
            foreach ($paths as $file => $count) {
                if ($count !== $numFiles) {
                    unset($paths[$file]);
                    continue;
                }
                $fileHash = substr(sha1($file), 0, 12);
                $testFile = $cacheDir . \DIRECTORY_SEPARATOR . "phpcs.{$fileHash}.{$cacheHash}.cache";
                if ($cacheFile === null) {
                    // This will be our default location if we can't find
                    // an existing file.
                    $cacheFile = $testFile;
                }
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    StatusWriter::write("=> {$testFile}", 2);
                    StatusWriter::write(" * based on shared location: {$file} *", 3);
                }
                if (file_exists($testFile) === \true) {
                    $cacheFile = $testFile;
                    break;
                }
            }
            if ($cacheFile === null) {
                // Unlikely, but just in case $paths is empty for some reason.
                $cacheFile = $cacheDir . \DIRECTORY_SEPARATOR . "phpcs.{$cacheHash}.cache";
            }
        }
        self::$path = $cacheFile;
        if (\PHP_CODESNIFFER_VERBOSITY > 1) {
            StatusWriter::write('=> Using cache file: ' . self::$path, 1);
        }
        if (file_exists(self::$path) === \true) {
            self::$cache = json_decode(file_get_contents(self::$path), \true);
            // Verify the contents of the cache file.
            if (self::$cache['config'] !== $configData) {
                self::$cache = [];
                if (\PHP_CODESNIFFER_VERBOSITY > 1) {
                    StatusWriter::write('* cache was invalid and has been cleared *', 1);
                }
            }
        } elseif (\PHP_CODESNIFFER_VERBOSITY > 1) {
            StatusWriter::write('* cache file does not exist *', 1);
        }
        self::$cache['config'] = $configData;
    }
    /**
     * Saves the current cache to the filesystem.
     *
     * @return void
     */
    public static function save()
    {
        file_put_contents(self::$path, json_encode(self::$cache));
    }
    /**
     * Retrieves a single entry from the cache.
     *
     * @param string|null $key The key of the data to get. If NULL,
     *                         everything in the cache is returned.
     *
     * @return mixed
     */
    public static function get(?string $key = null)
    {
        if ($key === null) {
            return self::$cache;
        }
        if (isset(self::$cache[$key]) === \true) {
            return self::$cache[$key];
        }
        return \false;
    }
    /**
     * Retrieves a single entry from the cache.
     *
     * @param string|null $key   The key of the data to set. If NULL,
     *                           sets the entire cache.
     * @param mixed       $value The value to set.
     *
     * @return void
     */
    public static function set(?string $key, $value)
    {
        if ($key === null) {
            self::$cache = $value;
        } else {
            self::$cache[$key] = $value;
        }
    }
    /**
     * Retrieves the number of cache entries.
     *
     * @return int
     */
    public static function getSize()
    {
        return count(self::$cache) - 1;
    }
}
