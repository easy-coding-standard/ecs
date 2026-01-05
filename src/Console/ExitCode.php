<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Console;

use Symfony\Component\Console\Command\Command;

final class ExitCode
{
    public const int SUCCESS = Command::SUCCESS;

    public const int FAILURE = Command::FAILURE;

    public const int CHANGED_CODE_OR_FOUND_ERRORS = 2;
}
