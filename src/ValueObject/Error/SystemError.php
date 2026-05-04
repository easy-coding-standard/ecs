<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\ValueObject\Error;

use Symplify\EasyCodingStandard\Parallel\ValueObject\Name;
use ECSPrefix202605\Symplify\EasyParallel\Contract\SerializableInterface;
final class SystemError implements SerializableInterface
{
    /**
     * @readonly
     * @var int
     */
    private $line;
    /**
     * @readonly
     * @var string
     */
    private $message;
    /**
     * @readonly
     * @var string
     */
    private $relativeFilePath;
    public function __construct(int $line, string $message, string $relativeFilePath)
    {
        $this->line = $line;
        $this->message = $message;
        $this->relativeFilePath = $relativeFilePath;
    }
    public function getMessage(): string
    {
        return $this->message;
    }
    public function getFileWithLine(): string
    {
        return $this->relativeFilePath . ':' . $this->line;
    }
    /**
     * @return array{line: int, message: string, relative_file_path: string}
     */
    public function jsonSerialize(): array
    {
        return [Name::LINE => $this->line, Name::MESSAGE => $this->message, Name::RELATIVE_FILE_PATH => $this->relativeFilePath];
    }
    /**
     * @param array{line: int, message: string, relative_file_path: string} $json
     * @return $this
     */
    public static function decode(array $json): \ECSPrefix202605\Symplify\EasyParallel\Contract\SerializableInterface
    {
        return new self($json[Name::LINE], $json[Name::MESSAGE], $json[Name::RELATIVE_FILE_PATH]);
    }
}
