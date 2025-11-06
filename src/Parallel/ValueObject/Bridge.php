<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Parallel\ValueObject;

/**
 * @enum
 */
final class Bridge
{
    /**
     * @var string
     */
    public const FILE_DIFFS = 'file_diffs';
    /**
     * @var string
     */
    public const CODING_STANDARD_ERRORS = 'coding_standard_errors';
    /**
     * @var string
     */
    public const SYSTEM_ERRORS = 'system_errors';
    /**
     * @var string
     */
    public const SYSTEM_ERRORS_COUNT = 'system_errors_count';
    /**
     * @var string
     */
    public const FILES_COUNT = 'files_count';
}
