<?php

/**
 * An abstract filter class for checking files and folders against exact matches.
 *
 * Supports both allowed files and disallowed files.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Filters;

use PHP_CodeSniffer\Util\Common;
abstract class ExactMatch extends \PHP_CodeSniffer\Filters\Filter
{
    /**
     * A list of files to exclude.
     *
     * @var array
     */
    private $disallowedFiles = null;
    /**
     * A list of files to include.
     *
     * If the allowed files list is empty, only files in the disallowed files list will be excluded.
     *
     * @var array
     */
    private $allowedFiles = null;
    /**
     * Check whether the current element of the iterator is acceptable.
     *
     * If a file is both disallowed and allowed, it will be deemed unacceptable.
     *
     * @return bool
     */
    public function accept()
    {
        if (parent::accept() === \false) {
            return \false;
        }
        if ($this->disallowedFiles === null) {
            $this->disallowedFiles = $this->getDisallowedFiles();
        }
        if ($this->allowedFiles === null) {
            $this->allowedFiles = $this->getAllowedFiles();
        }
        $filePath = Common::realpath($this->current());
        // If a file is both disallowed and allowed, the disallowed files list takes precedence.
        if (isset($this->disallowedFiles[$filePath]) === \true) {
            return \false;
        }
        if (empty($this->allowedFiles) === \true && empty($this->disallowedFiles) === \false) {
            // We are only checking the disallowed files list, so everything else should be allowed.
            return \true;
        }
        return isset($this->allowedFiles[$filePath]);
    }
    /**
     * Returns an iterator for the current entry.
     *
     * Ensures that the disallowed files list and the allowed files list are preserved so they don't have
     * to be generated each time.
     *
     * @return \RecursiveIterator
     */
    public function getChildren()
    {
        $children = parent::getChildren();
        $children->disallowedFiles = $this->disallowedFiles;
        $children->allowedFiles = $this->allowedFiles;
        return $children;
    }
    /**
     * Get a list of file paths to exclude.
     *
     * @since 3.9.0 Replaces the `getBlacklist()` method, which was removed in PHPCS 4.0.0.
     *
     * @return array
     */
    abstract protected function getDisallowedFiles();
    /**
     * Get a list of file paths to include.
     *
     * @since 3.9.0 Replaces the `getWhitelist()` method, which was removed in PHPCS 4.0.0.
     *
     * @return array
     */
    abstract protected function getAllowedFiles();
}
