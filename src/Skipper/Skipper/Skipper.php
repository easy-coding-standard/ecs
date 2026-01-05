<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Skipper\Skipper;

use Symplify\EasyCodingStandard\Skipper\Contract\SkipVoterInterface;
use Symplify\EasyCodingStandard\Skipper\SkipVoter\ClassAndCodeSkipVoter;
use Symplify\EasyCodingStandard\Skipper\SkipVoter\ClassSkipVoter;
use Symplify\EasyCodingStandard\Skipper\SkipVoter\MessageSkipVoter;
use Symplify\EasyCodingStandard\Skipper\SkipVoter\PathSkipVoter;

/**
 * @api
 * @see \Symplify\EasyCodingStandard\Tests\Skipper\Skipper\Skipper\SkipperTest
 */
final class Skipper
{
    private const string FILE_ELEMENT = 'file_elements';

    /**
     * @var SkipVoterInterface[]
     */
    private array $skipVoters = [];

    public function __construct(
        ClassAndCodeSkipVoter $classAndCodeSkipVoter,
        ClassSkipVoter $classSkipVoter,
        MessageSkipVoter $messageSkipVoter,
        PathSkipVoter $pathSkipVoter,
    ) {
        $this->skipVoters = [$classAndCodeSkipVoter, $classSkipVoter, $messageSkipVoter, $pathSkipVoter];
    }

    public function shouldSkipElement(string | object $element): bool
    {
        return $this->shouldSkipElementAndFilePath($element, __FILE__);
    }

    public function shouldSkipFilePath(string $filePath): bool
    {
        return $this->shouldSkipElementAndFilePath(self::FILE_ELEMENT, $filePath);
    }

    public function shouldSkipElementAndFilePath(string | object $element, string $filePath): bool
    {
        foreach ($this->skipVoters as $skipVoter) {
            if (! $skipVoter->match($element)) {
                continue;
            }

            if (! $skipVoter->shouldSkip($element, $filePath)) {
                continue;
            }

            return true;
        }

        return false;
    }
}
