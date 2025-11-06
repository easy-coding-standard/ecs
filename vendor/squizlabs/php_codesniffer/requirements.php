<?php

/**
 * Requirements check for PHP_CodeSniffer.
 *
 * :WARNING:
 *   This file MUST stay cross-version compatible with older PHP versions (min: PHP 5.3) to allow
 *   for the requirements check to work correctly.
 *
 *   The PHP 5.3 minimum is set as the previous PHPCS major (3.x) already had a PHP 5.4 minimum
 *   requirement and didn't take parse errors caused due to the use of namespaces into account
 *   in its requirements check, so running PHPCS 3.x on PHP < 5.3 would have failed with a parse
 *   error already anyway, so PHP 5.3 seems reasonable to keep as the minimum for this.
 * :WARNING:
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Juliette Reinders Folmer <phpcs_nospam@adviesenzo.nl>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer;

/**
 * Exits if the minimum requirements of PHP_CodeSniffer are not met.
 *
 * @return void
 */
function checkRequirements()
{
    // IMPORTANT: Must stay in sync with the value of the `PHP_CodeSniffer\Util\ExitCode::REQUIREMENTS_NOT_MET` constant!
    $exitCode = 64;
    // Check the PHP version.
    if (\PHP_VERSION_ID < 70200) {
        $error = 'ERROR: PHP_CodeSniffer requires PHP version 7.2.0 or greater.' . \PHP_EOL;
        \fwrite(\STDERR, $error);
        exit($exitCode);
    }
    $requiredExtensions = array('tokenizer', 'xmlwriter', 'SimpleXML');
    $missingExtensions = array();
    foreach ($requiredExtensions as $extension) {
        if (\extension_loaded($extension) === \false) {
            $missingExtensions[] = $extension;
        }
    }
    if (empty($missingExtensions) === \false) {
        $last = \array_pop($requiredExtensions);
        $required = \implode(', ', $requiredExtensions);
        $required .= ' and ' . $last;
        if (\count($missingExtensions) === 1) {
            $missing = $missingExtensions[0];
        } else {
            $last = \array_pop($missingExtensions);
            $missing = \implode(', ', $missingExtensions);
            $missing .= ' and ' . $last;
        }
        $error = 'ERROR: PHP_CodeSniffer requires the %s extensions to be enabled. Please enable %s.' . \PHP_EOL;
        \fwrite(\STDERR, \sprintf($error, $required, $missing));
        exit($exitCode);
    }
}
