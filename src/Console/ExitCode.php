<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Console;

use ECSPrefix202601\Symfony\Component\Console\Command\Command;
final class ExitCode
{
    /**
     * @var int
     */
    public const SUCCESS = Command::SUCCESS;
    /**
     * @var int
     */
    public const FAILURE = Command::FAILURE;
    /**
     * @var int
     */
    public const CHANGED_CODE_OR_FOUND_ERRORS = 2;
}
