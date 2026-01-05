<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Console\Output;

use Symplify\EasyCodingStandard\Console\ExitCode;
use Symplify\EasyCodingStandard\ValueObject\Configuration;
use Symplify\EasyCodingStandard\ValueObject\Error\ErrorAndDiffResult;
final class ExitCodeResolver
{
    /**
     * @return ExitCode::*
     */
    public function resolve(ErrorAndDiffResult $errorAndDiffResult, Configuration $configuration) : int
    {
        if ($errorAndDiffResult->getErrorCount() === 0 && $errorAndDiffResult->getFileDiffsCount() === 0) {
            return ExitCode::SUCCESS;
        }
        if ($configuration->isFixer()) {
            return $errorAndDiffResult->getErrorCount() === 0 ? ExitCode::SUCCESS : ExitCode::FAILURE;
        }
        return $errorAndDiffResult->getErrorCount() !== 0 || $errorAndDiffResult->getFileDiffsCount() !== 0 ? ExitCode::CHANGED_CODE_OR_FOUND_ERRORS : ExitCode::SUCCESS;
    }
}
