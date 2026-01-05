<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Testing\Contract;

interface ConfigAwareInterface
{
    public function provideConfig() : string;
}
