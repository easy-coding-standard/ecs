<?php

declare (strict_types=1);
/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace PhpCsFixer\Linter;

use PhpCsFixer\Hasher;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class CachingLinter implements \PhpCsFixer\Linter\LinterInterface
{
    /**
     * @var \PhpCsFixer\Linter\LinterInterface
     */
    private $sublinter;
    /**
     * @var array<string, LintingResultInterface>
     */
    private $cache = [];
    public function __construct(\PhpCsFixer\Linter\LinterInterface $linter)
    {
        $this->sublinter = $linter;
    }
    public function isAsync() : bool
    {
        return $this->sublinter->isAsync();
    }
    public function lintFile(string $path) : \PhpCsFixer\Linter\LintingResultInterface
    {
        $checksum = Hasher::calculate(\file_get_contents($path));
        return $this->cache[$checksum] = $this->cache[$checksum] ?? $this->sublinter->lintFile($path);
    }
    public function lintSource(string $source) : \PhpCsFixer\Linter\LintingResultInterface
    {
        $checksum = Hasher::calculate($source);
        return $this->cache[$checksum] = $this->cache[$checksum] ?? $this->sublinter->lintSource($source);
    }
}
