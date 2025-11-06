<?php

declare (strict_types=1);
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
    /**
     * @var string
     */
    private const FILE_ELEMENT = 'file_elements';
    /**
     * @var SkipVoterInterface[]
     */
    private $skipVoters = [];
    public function __construct(ClassAndCodeSkipVoter $classAndCodeSkipVoter, ClassSkipVoter $classSkipVoter, MessageSkipVoter $messageSkipVoter, PathSkipVoter $pathSkipVoter)
    {
        $this->skipVoters = [$classAndCodeSkipVoter, $classSkipVoter, $messageSkipVoter, $pathSkipVoter];
    }
    /**
     * @param string|object $element
     */
    public function shouldSkipElement($element) : bool
    {
        return $this->shouldSkipElementAndFilePath($element, __FILE__);
    }
    public function shouldSkipFilePath(string $filePath) : bool
    {
        return $this->shouldSkipElementAndFilePath(self::FILE_ELEMENT, $filePath);
    }
    /**
     * @param string|object $element
     */
    public function shouldSkipElementAndFilePath($element, string $filePath) : bool
    {
        foreach ($this->skipVoters as $skipVoter) {
            if (!$skipVoter->match($element)) {
                continue;
            }
            if (!$skipVoter->shouldSkip($element, $filePath)) {
                continue;
            }
            return \true;
        }
        return \false;
    }
}
