<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\TokenRunner\ValueObject;

final class BlockInfo
{
    /**
     * @readonly
     * @var int
     */
    private $start;
    /**
     * @readonly
     * @var int
     */
    private $end;
    public function __construct(int $start, int $end)
    {
        $this->start = $start;
        $this->end = $end;
    }
    public function getStart() : int
    {
        return $this->start;
    }
    public function getEnd() : int
    {
        return $this->end;
    }
}
