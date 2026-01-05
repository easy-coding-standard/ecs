<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\ValueObject;

final class Option
{
    public const string FIX = 'fix';

    public const string CLEAR_CACHE = 'clear-cache';

    public const string NO_PROGRESS_BAR = 'no-progress-bar';

    public const string NO_ERROR_TABLE = 'no-error-table';

    public const string OUTPUT_FORMAT = 'output-format';

    public const string NO_DIFFS = 'no-diffs';

    /**
     * @api
     * @deprecated Use @see \Symplify\EasyCodingStandard\Config\ECSConfig::skip()
     */
    public const string SKIP = 'skip';

    /**
     * Alias for SOURCE
     *
     * @api
     * @deprecated Use @see \Symplify\EasyCodingStandard\Config\ECSConfig::paths()
     */
    public const string PATHS = 'paths';

    /**
     * @api
     * @deprecated Use @see \Symplify\EasyCodingStandard\Config\ECSConfig::skip()
     */
    public const string ONLY = 'only';

    /**
     * @deprecated Use @see \Symplify\EasyCodingStandard\Config\ECSConfig::cacheDirectory()
     */
    public const string CACHE_DIRECTORY = 'cache_directory';

    /**
     * @deprecated Use @see \Symplify\EasyCodingStandard\Config\ECSConfig::lineEnding()
     */
    public const string LINE_ENDING = 'line_ending';

    /**
     * @deprecated Use @see \Symplify\EasyCodingStandard\Config\ECSConfig::indentation()
     */
    public const string INDENTATION = 'indentation';

    /**
     * @deprecated Use @see \Symplify\EasyCodingStandard\Config\ECSConfig::cacheNamespace()
     */
    public const string CACHE_NAMESPACE = 'cache_namespace';

    /**
     * @deprecated Use @see \Symplify\EasyCodingStandard\Config\ECSConfig::fileExtensions()
     */
    public const string FILE_EXTENSIONS = 'file_extensions';

    /**
     * @api
     */
    public const string INDENTATION_SPACES = 'spaces';

    /**
     * @api
     */
    public const string INDENTATION_TAB = 'tab';

    public const string XDEBUG = 'xdebug';

    public const string DEBUG = 'debug';

    /**
     * @see \Symplify\EasyCodingStandard\Config\ECSConfig::parallel()
     */
    public const string PARALLEL = 'parallel';

    public const string CONFIG = 'config';

    /**
     * @see \Symplify\EasyCodingStandard\Config\ECSConfig::parallel()
     */
    public const string PARALLEL_JOB_SIZE = 'parallel_job_size';

    public const string PARALLEL_PORT = 'port';

    public const string PARALLEL_IDENTIFIER = 'identifier';

    /**
     * @see \Symplify\EasyCodingStandard\Config\ECSConfig::parallel()
     */
    public const string PARALLEL_MAX_NUMBER_OF_PROCESSES = 'max-number-of-processes';

    public const string MEMORY_LIMIT = 'memory-limit';

    /**
     * @see \Symplify\EasyCodingStandard\Config\ECSConfig::parallel()
     */
    public const string PARALLEL_TIMEOUT_IN_SECONDS = 'parallel-timeout-in-seconds';

    /**
     * @see \Symplify\EasyCodingStandard\Config\ECSConfig::reportingRealPath()
     */
    public const string REPORTING_REALPATH = 'reporting-realpath';
}
