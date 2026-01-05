<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Skipper\SkipVoter;

use Symplify\EasyCodingStandard\Skipper\Contract\SkipVoterInterface;
use Symplify\EasyCodingStandard\Skipper\SkipCriteriaResolver\SkippedClassResolver;
use Symplify\EasyCodingStandard\Skipper\Skipper\SkipSkipper;
final class ClassSkipVoter implements SkipVoterInterface
{
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\Skipper\Skipper\SkipSkipper
     */
    private $skipSkipper;
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\Skipper\SkipCriteriaResolver\SkippedClassResolver
     */
    private $skippedClassResolver;
    public function __construct(SkipSkipper $skipSkipper, SkippedClassResolver $skippedClassResolver)
    {
        $this->skipSkipper = $skipSkipper;
        $this->skippedClassResolver = $skippedClassResolver;
    }
    /**
     * @param string|object $element
     */
    public function match($element) : bool
    {
        if (\is_object($element)) {
            return \true;
        }
        return \class_exists($element) || \interface_exists($element);
    }
    /**
     * @param string|object $element
     * @param \SplFileInfo|string $file
     */
    public function shouldSkip($element, $file) : bool
    {
        $skippedClasses = $this->skippedClassResolver->resolve();
        return $this->skipSkipper->doesMatchSkip($element, $file, $skippedClasses);
    }
}
