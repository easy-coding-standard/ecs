<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\SniffRunner\DataCollector;

use PHP_CodeSniffer\Sniffs\Sniff;
use Symplify\EasyCodingStandard\SniffRunner\ValueObject\Error\CodingStandardError;
final class SniffMetadataCollector
{
    /**
     * @var array<class-string|string>
     */
    private $appliedSniffs = [];
    /**
     * @var CodingStandardError[]
     */
    private $codingStandardErrors = [];
    /**
     * @param class-string<Sniff>|string $checkerClass
     */
    public function addAppliedSniff(string $checkerClass) : void
    {
        $this->appliedSniffs[] = $checkerClass;
    }
    /**
     * @return array<class-string<Sniff>|string>
     */
    public function getAppliedSniffs() : array
    {
        return $this->appliedSniffs;
    }
    public function reset() : void
    {
        $this->appliedSniffs = [];
        $this->codingStandardErrors = [];
    }
    public function resetErrors() : void
    {
        $this->codingStandardErrors = [];
    }
    public function addCodingStandardError(CodingStandardError $codingStandardError) : void
    {
        $this->codingStandardErrors[] = $codingStandardError;
    }
    /**
     * @return CodingStandardError[]
     */
    public function getCodingStandardErrors() : array
    {
        return $this->codingStandardErrors;
    }
}
