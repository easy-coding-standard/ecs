<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\TokenRunner\ValueObject;

final class LineLengthAndPosition
{
    /**
     * @readonly
     * @var int
     */
    private $lineLength;
    /**
     * @readonly
     * @var int
     */
    private $currentPosition;
    public function __construct(int $lineLength, int $currentPosition)
    {
        $this->lineLength = $lineLength;
        $this->currentPosition = $currentPosition;
    }
    public function getLineLength() : int
    {
        return $this->lineLength;
    }
    public function getCurrentPosition() : int
    {
        return $this->currentPosition;
    }
}
