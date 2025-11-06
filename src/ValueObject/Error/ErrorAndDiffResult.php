<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\ValueObject\Error;

use Symplify\EasyCodingStandard\SniffRunner\ValueObject\Error\CodingStandardError;
final class ErrorAndDiffResult
{
    /**
     * @var array<SystemError|string>
     * @readonly
     */
    private $systemErrors;
    /**
     * @var CodingStandardError[]
     */
    private $codingStandardErrors = [];
    /**
     * @var FileDiff[]
     */
    private $fileDiffs = [];
    /**
     * @param CodingStandardError[] $codingStandardErrors
     * @param FileDiff[] $fileDiffs
     * @param array<SystemError|string> $systemErrors
     */
    public function __construct(array $codingStandardErrors, array $fileDiffs, array $systemErrors)
    {
        $this->systemErrors = $systemErrors;
        $this->codingStandardErrors = $this->sortByFileAndLine($codingStandardErrors);
        $this->fileDiffs = $this->sortByFilePath($fileDiffs);
    }
    public function getErrorCount() : int
    {
        return $this->getCodingStandardErrorCount() + \count($this->systemErrors);
    }
    public function getCodingStandardErrorCount() : int
    {
        return \count($this->codingStandardErrors);
    }
    public function getFileDiffsCount() : int
    {
        return \count($this->fileDiffs);
    }
    /**
     * @return CodingStandardError[]
     */
    public function getErrors() : array
    {
        return $this->codingStandardErrors;
    }
    /**
     * @return array<SystemError|string>
     */
    public function getSystemErrors() : array
    {
        return $this->systemErrors;
    }
    /**
     * @return FileDiff[]
     */
    public function getFileDiffs() : array
    {
        return $this->fileDiffs;
    }
    /**
     * @param CodingStandardError[] $errorMessages
     * @return CodingStandardError[]
     */
    private function sortByFileAndLine(array $errorMessages) : array
    {
        \usort($errorMessages, static function (CodingStandardError $firstCodingStandardError, CodingStandardError $secondCodingStandardError) : int {
            return [$firstCodingStandardError->getRelativeFilePath(), $firstCodingStandardError->getLine()] <=> [$secondCodingStandardError->getRelativeFilePath(), $secondCodingStandardError->getLine()];
        });
        return $errorMessages;
    }
    /**
     * @param FileDiff[] $fileDiffs
     * @return FileDiff[]
     */
    private function sortByFilePath(array $fileDiffs) : array
    {
        \uasort($fileDiffs, static function (\Symplify\EasyCodingStandard\ValueObject\Error\FileDiff $firstFileDiff, \Symplify\EasyCodingStandard\ValueObject\Error\FileDiff $secondFileDiff) : int {
            return $firstFileDiff->getRelativeFilePath() <=> $secondFileDiff->getRelativeFilePath();
        });
        return $fileDiffs;
    }
}
