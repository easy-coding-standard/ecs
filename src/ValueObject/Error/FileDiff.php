<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\ValueObject\Error;

use PHP_CodeSniffer\Sniffs\Sniff;
use PhpCsFixer\Fixer\FixerInterface;
use Symplify\EasyCodingStandard\Parallel\ValueObject\Name;
use ECSPrefix202605\Symplify\EasyParallel\Contract\SerializableInterface;
final class FileDiff implements SerializableInterface
{
    /**
     * @readonly
     * @var string
     */
    private $relativeFilePath;
    /**
     * @readonly
     * @var string
     */
    private $diff;
    /**
     * @readonly
     * @var string
     */
    private $consoleFormattedDiff;
    /**
     * @var array<class-string<Sniff|FixerInterface>|string>
     */
    private $appliedCheckers;
    /**
     * @param array<class-string<Sniff|FixerInterface>|string> $appliedCheckers
     */
    public function __construct(string $relativeFilePath, string $diff, string $consoleFormattedDiff, array $appliedCheckers)
    {
        $this->relativeFilePath = $relativeFilePath;
        $this->diff = $diff;
        $this->consoleFormattedDiff = $consoleFormattedDiff;
        $this->appliedCheckers = $appliedCheckers;
    }
    public function getDiff(): string
    {
        return $this->diff;
    }
    public function getDiffConsoleFormatted(): string
    {
        return $this->consoleFormattedDiff;
    }
    /**
     * @return array<class-string<Sniff|FixerInterface>|string>
     */
    public function getAppliedCheckers(): array
    {
        $this->appliedCheckers = array_unique($this->appliedCheckers);
        sort($this->appliedCheckers);
        return $this->appliedCheckers;
    }
    public function getRelativeFilePath(): string
    {
        return $this->relativeFilePath;
    }
    public function getAbsoluteFilePath(): ?string
    {
        return \realpath($this->relativeFilePath) ?: null;
    }
    /**
     * @return array{relative_file_path: string, diff: string, diff_console_formatted: string, applied_checkers: string[]}
     */
    public function jsonSerialize(): array
    {
        return [Name::ABSOLUTE_FILE_PATH => $this->getAbsoluteFilePath(), Name::RELATIVE_FILE_PATH => $this->relativeFilePath, Name::DIFF => $this->diff, Name::DIFF_CONSOLE_FORMATTED => $this->consoleFormattedDiff, Name::APPLIED_CHECKERS => $this->getAppliedCheckers()];
    }
    /**
     * @param array{relative_file_path: string, diff: string, diff_console_formatted: string, applied_checkers: string[]} $json
     * @return $this
     */
    public static function decode(array $json): \ECSPrefix202605\Symplify\EasyParallel\Contract\SerializableInterface
    {
        return new self($json[Name::RELATIVE_FILE_PATH], $json[Name::DIFF], $json[Name::DIFF_CONSOLE_FORMATTED], $json[Name::APPLIED_CHECKERS]);
    }
}
