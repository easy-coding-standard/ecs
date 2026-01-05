<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Parallel\ValueObject;

/**
 * @enum
 */
final class Bridge
{
    public const string FILE_DIFFS = 'file_diffs';

    public const string CODING_STANDARD_ERRORS = 'coding_standard_errors';

    public const string SYSTEM_ERRORS = 'system_errors';

    public const string SYSTEM_ERRORS_COUNT = 'system_errors_count';

    public const string FILES_COUNT = 'files_count';
}
