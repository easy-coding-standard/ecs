<?php

/**
 * An exception thrown by PHP_CodeSniffer when it wants to exit from somewhere not in the main runner.
 *
 * Allows the runner to return an exit code instead of putting exit codes elsewhere
 * in the source code.
 *
 * Exit codes passed to this exception (as the `$code` parameter) MUST be one of the
 * predefined exit code constants per the `PHP_CodeSniffer\Util\ExitCode` class; or a bitmask sum of those.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Juliette Reinders Folmer <phpcs_nospam@adviesenzo.nl>
 * @copyright 2006-2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @copyright 2023 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/HEAD/licence.txt BSD Licence
 */
namespace PHP_CodeSniffer\Exceptions;

use Exception;
class DeepExitException extends Exception
{
}
