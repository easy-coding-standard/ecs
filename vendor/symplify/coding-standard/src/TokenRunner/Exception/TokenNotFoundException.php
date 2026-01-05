<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\TokenRunner\Exception;

use Exception;
final class TokenNotFoundException extends Exception
{
    public function __construct(int $position)
    {
        $message = \sprintf('Token on position %d was not found', $position);
        parent::__construct($message);
    }
}
