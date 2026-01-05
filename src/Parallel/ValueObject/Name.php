<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Parallel\ValueObject;

/**
 * Helpers constant for passing constant names around
 */
final class Name
{
    public const string LINE = 'line';

    public const string MESSAGE = 'message';

    public const string CHECKER_CLASS = 'checker_class';

    public const string RELATIVE_FILE_PATH = 'relative_file_path';

    public const string ABSOLUTE_FILE_PATH = 'absolute_file_path';

    public const string DIFF = 'diff';

    public const string DIFF_CONSOLE_FORMATTED = 'diff_console_formatted';

    public const string APPLIED_CHECKERS = 'applied_checkers';
}
