<?php

/**
 * Class to manage a list of sniffs to ignore.
 *
 * @copyright 2025 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Util;

/**
 * Class to manage a list of sniffs to ignore.
 *
 * ---------------------------------------------------------------------------------------------
 * This class is intended for internal use only and is not part of the public API.
 * This also means that it has no promise of backward compatibility. Use at your own risk.
 * ---------------------------------------------------------------------------------------------
 *
 * @internal
 */
final class IgnoreList
{
    /**
     * Ignore data.
     *
     * Data is a tree, standard → category → sniff → code.
     * Each level may be a boolean indicating that everything underneath the branch is or is not ignored, or
     * may have a `.default' key indicating the default status for any branches not in the tree.
     *
     * @var array<string, bool|array<string, bool|array<string, bool|array<string, bool>>>>
     */
    private $data = ['.default' => \false];
    /**
     * Prohibit direct instantiation of this class. Use the static `get[New]Instance*()` entry point methods instead.
     */
    private function __construct()
    {
    }
    /**
     * Get an instance set to ignore nothing.
     *
     * @return \PHP_CodeSniffer\Util\IgnoreList
     */
    public static function getInstanceIgnoringNothing()
    {
        return new self();
    }
    /**
     * Get an instance set to ignore everything.
     *
     * @return \PHP_CodeSniffer\Util\IgnoreList
     */
    public static function getInstanceIgnoringAll()
    {
        $instance = new self();
        $instance->data['.default'] = \true;
        return $instance;
    }
    /**
     * Get a new instance based on an existing instance.
     *
     * If passed null, creates a new instance that ignores nothing.
     *
     * @param \PHP_CodeSniffer\Util\IgnoreList|null $ignoreList List to clone.
     *
     * @return \PHP_CodeSniffer\Util\IgnoreList
     */
    public static function getNewInstanceFrom(?\PHP_CodeSniffer\Util\IgnoreList $ignoreList)
    {
        if ($ignoreList === null) {
            return self::getInstanceIgnoringNothing();
        }
        return clone $ignoreList;
    }
    /**
     * Set the ignore status for a sniff.
     *
     * @param string $code   Partial or complete sniff code.
     * @param bool   $ignore Whether the specified sniff should be ignored.
     *
     * @return \PHP_CodeSniffer\Util\IgnoreList $this for chaining.
     */
    public function set(string $code, bool $ignore)
    {
        $data =& $this->data;
        $parts = \explode('.', $code);
        while (\count($parts) > 1) {
            $part = \array_shift($parts);
            if (isset($data[$part]) === \false) {
                $data[$part] = [];
            } elseif (\is_bool($data[$part]) === \true) {
                $data[$part] = ['.default' => $data[$part]];
            }
            $data =& $data[$part];
        }
        $part = \array_shift($parts);
        $data[$part] = (bool) $ignore;
        return $this;
    }
    /**
     * Check whether a sniff code is ignored.
     *
     * @param string $code Partial or complete sniff code.
     *
     * @return bool
     */
    public function isIgnored(string $code)
    {
        $data = $this->data;
        $returnValue = $data['.default'];
        foreach (\explode('.', $code) as $part) {
            if (isset($data[$part]) === \false) {
                break;
            }
            $data = $data[$part];
            if (\is_bool($data) === \true) {
                $returnValue = $data;
                break;
            }
            if (isset($data['.default']) === \true) {
                $returnValue = $data['.default'];
            }
        }
        return $returnValue;
    }
    /**
     * Check if the list ignores nothing.
     *
     * @return bool
     */
    public function ignoresNothing()
    {
        $arraysToProcess = [$this->data];
        while ($arraysToProcess !== []) {
            $arrayBeingProcessed = \array_pop($arraysToProcess);
            foreach ($arrayBeingProcessed as $valueBeingProcessed) {
                if ($valueBeingProcessed === \true) {
                    return \false;
                }
                if (\is_array($valueBeingProcessed) === \true) {
                    $arraysToProcess[] = $valueBeingProcessed;
                }
            }
        }
        return \true;
    }
    /**
     * Check if the list ignores everything.
     *
     * @return bool
     */
    public function ignoresEverything()
    {
        $arraysToProcess = [$this->data];
        while ($arraysToProcess !== []) {
            $arrayBeingProcessed = \array_pop($arraysToProcess);
            foreach ($arrayBeingProcessed as $valueBeingProcessed) {
                if ($valueBeingProcessed === \false) {
                    return \false;
                }
                if (\is_array($valueBeingProcessed) === \true) {
                    $arraysToProcess[] = $valueBeingProcessed;
                }
            }
        }
        return \true;
    }
}
