<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Skipper\SkipVoter;

use Symplify\EasyCodingStandard\Skipper\Contract\SkipVoterInterface;
use Symplify\EasyCodingStandard\Skipper\Matcher\FileInfoMatcher;
use Symplify\EasyCodingStandard\Skipper\SkipCriteriaResolver\SkippedPathsResolver;
final class PathSkipVoter implements SkipVoterInterface
{
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\Skipper\Matcher\FileInfoMatcher
     */
    private $fileInfoMatcher;
    /**
     * @readonly
     * @var \Symplify\EasyCodingStandard\Skipper\SkipCriteriaResolver\SkippedPathsResolver
     */
    private $skippedPathsResolver;
    public function __construct(FileInfoMatcher $fileInfoMatcher, SkippedPathsResolver $skippedPathsResolver)
    {
        $this->fileInfoMatcher = $fileInfoMatcher;
        $this->skippedPathsResolver = $skippedPathsResolver;
    }
    /**
     * @param string|object $element
     */
    public function match($element) : bool
    {
        return \true;
    }
    /**
     * @param string|object $element
     * @param \SplFileInfo|string $file
     */
    public function shouldSkip($element, $file) : bool
    {
        $skippedPaths = $this->skippedPathsResolver->resolve();
        return $this->fileInfoMatcher->doesFileInfoMatchPatterns($file, $skippedPaths);
    }
}
