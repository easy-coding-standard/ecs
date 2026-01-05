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
namespace PhpCsFixer\Runner\Parallel;

use ECSPrefix202601\Fidry\CpuCoreCounter\CpuCoreCounter;
use ECSPrefix202601\Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder;
use ECSPrefix202601\Fidry\CpuCoreCounter\Finder\FinderRegistry;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ParallelConfigFactory
{
    /**
     * @var \Fidry\CpuCoreCounter\CpuCoreCounter|null
     */
    private static $cpuDetector;
    private function __construct()
    {
    }
    public static function sequential() : \PhpCsFixer\Runner\Parallel\ParallelConfig
    {
        return new \PhpCsFixer\Runner\Parallel\ParallelConfig(1);
    }
    /**
     * @param null|positive-int $filesPerProcess
     * @param null|positive-int $processTimeout
     * @param null|positive-int $maxProcesses
     */
    public static function detect(?int $filesPerProcess = null, ?int $processTimeout = null, ?int $maxProcesses = null) : \PhpCsFixer\Runner\Parallel\ParallelConfig
    {
        if (null === self::$cpuDetector) {
            self::$cpuDetector = new CpuCoreCounter(\array_merge(FinderRegistry::getDefaultLogicalFinders(), [new DummyCpuCoreFinder(1)]));
        }
        // Reserve 1 core for the main orchestrating process
        $available = self::$cpuDetector->getAvailableForParallelisation(1, $maxProcesses);
        return new \PhpCsFixer\Runner\Parallel\ParallelConfig($available->availableCpus, $filesPerProcess ?? \PhpCsFixer\Runner\Parallel\ParallelConfig::DEFAULT_FILES_PER_PROCESS, $processTimeout ?? \PhpCsFixer\Runner\Parallel\ParallelConfig::DEFAULT_PROCESS_TIMEOUT);
    }
}
