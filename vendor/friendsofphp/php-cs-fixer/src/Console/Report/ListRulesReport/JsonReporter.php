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
namespace PhpCsFixer\Console\Report\ListRulesReport;

use PhpCsFixer\Fixer\FixerInterface;
/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class JsonReporter implements \PhpCsFixer\Console\Report\ListRulesReport\ReporterInterface
{
    public function getFormat() : string
    {
        return 'json';
    }
    public function generate(\PhpCsFixer\Console\Report\ListRulesReport\ReportSummary $reportSummary) : string
    {
        $fixers = $reportSummary->getFixers();
        \usort($fixers, static function (FixerInterface $a, FixerInterface $b) : int {
            return $a->getName() <=> $b->getName();
        });
        $json = ['rules' => []];
        foreach ($fixers as $fixer) {
            $name = $fixer->getName();
            $json['rules'][$name] = ['isRisky' => $fixer->isRisky(), 'name' => $name, 'summary' => $fixer->getDefinition()->getSummary()];
        }
        return \json_encode($json, \JSON_PRETTY_PRINT);
    }
}
