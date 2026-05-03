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
use PhpCsFixer\Documentation\DocumentationLocator;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerFactory;
use ECSPrefix202605\SebastianBergmann\Diff\Chunk;
use ECSPrefix202605\SebastianBergmann\Diff\Diff;
use ECSPrefix202605\SebastianBergmann\Diff\Line;
use ECSPrefix202605\SebastianBergmann\Diff\Parser;
use ECSPrefix202605\Symfony\Component\Console\Formatter\OutputFormatter;
/**
 * Generates a report according to gitlabs subset of codeclimate json files.
 *
 * @author Hans-Christian Otto <c.otto@suora.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @see https://github.com/codeclimate/platform/blob/master/spec/analyzers/SPEC.md#data-types
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class GitlabReporter implements \PhpCsFixer\Console\Report\FixReport\ReporterInterface
{
    /**
     * @var \SebastianBergmann\Diff\Parser
     */
    private $diffParser;
    /**
     * @var \PhpCsFixer\Documentation\DocumentationLocator
     */
    private $documentationLocator;
    /**
     * @var \PhpCsFixer\FixerFactory
     */
    private $fixerFactory;
    /**
     * @var array<string, FixerInterface>
     */
    private $fixers;
    public function __construct()
    {
        $this->diffParser = new Parser();
        $this->documentationLocator = new DocumentationLocator();
        $this->fixerFactory = new FixerFactory();
        $this->fixerFactory->registerBuiltInFixers();
        $this->fixers = $this->createFixers();
    }
    public function getFormat() : string
    {
        return 'gitlab';
    }
    /**
     * Process changed files array. Returns generated report.
     */
    public function generate(\PhpCsFixer\Console\Report\FixReport\ReportSummary $reportSummary) : string
    {
        $about = Application::getAbout();
        $report = [];
        foreach ($reportSummary->getChanged() as $fileName => $change) {
            foreach ($change['appliedFixers'] as $fixerName) {
                $fixer = $this->fixers[$fixerName] ?? null;
                $report[] = ['check_name' => 'PHP-CS-Fixer.' . $fixerName, 'description' => null !== $fixer ? $fixer->getDefinition()->getSummary() : 'PHP-CS-Fixer.' . $fixerName . ' (custom rule)', 'content' => ['body' => \sprintf("%s\n%s", $about, null !== $fixer ? \sprintf('Check [docs](https://cs.symfony.com/doc/rules/%s.html) for more information.', \substr($this->documentationLocator->getFixerDocumentationFileRelativePath($fixer), 0, -4)) : 'Check performed with a custom rule.')], 'categories' => ['Style'], 'fingerprint' => \md5($fileName . $fixerName), 'severity' => 'minor', 'location' => ['path' => $fileName, 'lines' => self::getLines($this->diffParser->parse($change['diff']))]];
            }
        }
        $jsonString = \json_encode($report, 0);
        if (\json_last_error() !== \JSON_ERROR_NONE) {
            throw new \Exception(\json_last_error_msg());
        }
        return $reportSummary->isDecoratedOutput() ? OutputFormatter::escape($jsonString) : $jsonString;
    }
    /**
     * @param list<Diff> $diffs
     *
     * @return array{begin: int, end: int}
     */
    private static function getLines(array $diffs) : array
    {
        if (isset($diffs[0])) {
            $firstDiff = $diffs[0];
            $firstChunk = \Closure::bind(static function (Diff $diff) {
                return \array_shift($diff->chunks);
            }, null, $firstDiff)($firstDiff);
            if ($firstChunk instanceof Chunk) {
                return self::getBeginEndForDiffChunk($firstChunk);
            }
        }
        return ['begin' => 0, 'end' => 0];
    }
    /**
     * @return array{begin: int, end: int}
     */
    private static function getBeginEndForDiffChunk(Chunk $chunk) : array
    {
        $start = \Closure::bind(static function (Chunk $chunk) : int {
            return $chunk->start;
        }, null, $chunk)($chunk);
        $startRange = \Closure::bind(static function (Chunk $chunk) : int {
            return $chunk->startRange;
        }, null, $chunk)($chunk);
        $lines = \Closure::bind(static function (Chunk $chunk) : array {
            return $chunk->lines;
        }, null, $chunk)($chunk);
        \assert(\count($lines) > 0);
        $firstModifiedLineOffset = array_find_key($lines, static function (Line $line) : bool {
            $type = \Closure::bind(static function (Line $line) : int {
                return $line->type;
            }, null, $line)($line);
            return Line::UNCHANGED !== $type;
        });
        \assert(\is_int($firstModifiedLineOffset));
        return [
            // offset the start by where the first line is actually modified
            'begin' => $start + $firstModifiedLineOffset,
            // it's not where last modification takes place, only where diff (with --context) ends
            'end' => $start + $startRange,
        ];
    }
    /**
     * @return array<string, FixerInterface>
     */
    private function createFixers() : array
    {
        $fixers = [];
        foreach ($this->fixerFactory->getFixers() as $fixer) {
            $fixers[$fixer->getName()] = $fixer;
        }
        \ksort($fixers);
        return $fixers;
    }
}
