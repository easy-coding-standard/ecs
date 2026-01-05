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
namespace PhpCsFixer\Runner\Event;

use ECSPrefix202601\Symfony\Contracts\EventDispatcher\Event;
/**
 * Event that is fired when Fixer starts analysis.
 *
 * @author Greg Korba <greg@codito.dev>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class AnalysisStarted extends Event
{
    public const NAME = 'fixer.analysis_started';
    public const MODE_SEQUENTIAL = 'sequential';
    public const MODE_PARALLEL = 'parallel';
    /** @var self::MODE_* */
    private $mode;
    /**
     * @var bool
     */
    private $dryRun;
    /**
     * @param self::MODE_* $mode
     */
    public function __construct(string $mode, bool $dryRun)
    {
        $this->mode = $mode;
        $this->dryRun = $dryRun;
    }
    public function getMode() : string
    {
        return $this->mode;
    }
    public function isDryRun() : bool
    {
        return $this->dryRun;
    }
}
