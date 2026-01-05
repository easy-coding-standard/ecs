<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\Fixer;

use PhpCsFixer\Fixer\FixerInterface;
use SplFileInfo;
/**
 * We could use native AbstractFixer here, but it runs magic setup of newlines/spaces in constructor and many other
 * methods. This keeps it simple :)
 */
abstract class AbstractSymplifyFixer implements FixerInterface
{
    public function getPriority() : int
    {
        return 0;
    }
    public function getName() : string
    {
        return static::class;
    }
    public function isRisky() : bool
    {
        return \false;
    }
    public function supports(SplFileInfo $file) : bool
    {
        return \true;
    }
}
