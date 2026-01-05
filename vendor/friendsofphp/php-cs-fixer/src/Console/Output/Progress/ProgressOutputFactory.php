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
namespace PhpCsFixer\Console\Output\Progress;

use PhpCsFixer\Console\Output\OutputContext;
/**
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ProgressOutputFactory
{
    /**
     * @var array<ProgressOutputType::*, class-string<ProgressOutputInterface>>
     */
    private const OUTPUT_TYPE_MAP = [\PhpCsFixer\Console\Output\Progress\ProgressOutputType::NONE => \PhpCsFixer\Console\Output\Progress\NullOutput::class, \PhpCsFixer\Console\Output\Progress\ProgressOutputType::DOTS => \PhpCsFixer\Console\Output\Progress\DotsOutput::class, \PhpCsFixer\Console\Output\Progress\ProgressOutputType::BAR => \PhpCsFixer\Console\Output\Progress\PercentageBarOutput::class];
    /**
     * @param ProgressOutputType::* $outputType
     */
    public function create(string $outputType, OutputContext $context) : \PhpCsFixer\Console\Output\Progress\ProgressOutputInterface
    {
        if (null === $context->getOutput()) {
            $outputType = \PhpCsFixer\Console\Output\Progress\ProgressOutputType::NONE;
        }
        if (!$this->isBuiltInType($outputType)) {
            throw new \InvalidArgumentException(\sprintf('Something went wrong, "%s" output type is not supported', $outputType));
        }
        $outputClass = self::OUTPUT_TYPE_MAP[$outputType];
        // @phpstan-ignore-next-line new.noConstructor
        return new $outputClass($context);
    }
    private function isBuiltInType(string $outputType) : bool
    {
        return \in_array($outputType, \PhpCsFixer\Console\Output\Progress\ProgressOutputType::all(), \true);
    }
}
