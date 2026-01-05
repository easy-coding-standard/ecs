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
namespace PhpCsFixer\Console\Report\FixReport;

use PhpCsFixer\Console\Application;
use ECSPrefix202601\Symfony\Component\Console\Formatter\OutputFormatter;
/**
 * @author Boris Gorbylev <ekho@ekho.name>
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class JsonReporter implements \PhpCsFixer\Console\Report\FixReport\ReporterInterface
{
    public function getFormat() : string
    {
        return 'json';
    }
    public function generate(\PhpCsFixer\Console\Report\FixReport\ReportSummary $reportSummary) : string
    {
        $jsonFiles = [];
        foreach ($reportSummary->getChanged() as $file => $fixResult) {
            $jsonFile = ['name' => $file];
            if ($reportSummary->shouldAddAppliedFixers()) {
                $jsonFile['appliedFixers'] = $fixResult['appliedFixers'];
            }
            if ('' !== $fixResult['diff']) {
                $jsonFile['diff'] = $fixResult['diff'];
            }
            $jsonFiles[] = $jsonFile;
        }
        $json = ['about' => Application::getAbout(), 'files' => $jsonFiles, 'time' => ['total' => \round($reportSummary->getTime() / 1000, 3)], 'memory' => \round($reportSummary->getMemory() / 1024 / 1024, 3)];
        $json = \json_encode($json, 0);
        if (\json_last_error() !== \JSON_ERROR_NONE) {
            throw new \Exception(\json_last_error_msg());
        }
        return $reportSummary->isDecoratedOutput() ? OutputFormatter::escape($json) : $json;
    }
}
