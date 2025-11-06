<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Skipper\Contract;

interface SkipVoterInterface
{
    /**
     * @param string|object $element
     */
    public function match($element) : bool;
    /**
     * @param string|object $element
     * @param \SplFileInfo|string $file
     */
    public function shouldSkip($element, $file) : bool;
}
