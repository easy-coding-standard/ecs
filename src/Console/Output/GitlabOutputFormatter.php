<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Console\Output;

use function array_map as map;
use function array_merge as merge;
use SebastianBergmann\Diff\Chunk;
use SebastianBergmann\Diff\Line;
use SebastianBergmann\Diff\Parser as DiffParser;
use Symplify\EasyCodingStandard\Console\ExitCode;
use Symplify\EasyCodingStandard\Console\Style\EasyCodingStandardStyle;
use Symplify\EasyCodingStandard\Contract\Console\Output\OutputFormatterInterface;
use Symplify\EasyCodingStandard\SniffRunner\ValueObject\Error\CodingStandardError;
use Symplify\EasyCodingStandard\ValueObject\Configuration;
use Symplify\EasyCodingStandard\ValueObject\Error\ErrorAndDiffResult;
use Symplify\EasyCodingStandard\ValueObject\Error\FileDiff;

/**
 * Generates a JSON file containing the Gitlab-supported variant of
 * "Code Climate" issues for all unresolved errors.
 *
 * This is compatible with the specs of:
 *   - Code Climate: ^0.3.1
 *   - Gitlab: ^16.11 || ^17
 *
 * Applied diffs are NOT reported, but unapplied diffs ARE. This allows
 * our users to choose between a CI/CD workflow where `--fix` is used to
 * automatically commit trivial corrections OR one where any errors, even
 * resolvable, cause the pipeline to fail.
 *
 * Unfortunately, because of the way sniffs apply fixes without provenance data
 * and with cascading effects, multiple major refactors would be required in
 * order to accurately report exactly which fixes apply to exactly which lines.
 *
 * Finally, all reported errors will be marked as "minor" severity, since
 * DevOps downstream can choose if CI/CD ignores and our Sniffers don't
 * currently provide their own levels.
 *
 * As a warning to future maintainers, I believe Gitlab's documentation may be
 * slightly wrong. It's not a subset of the Code Climate format as insinuated,
 * since it changes some fields from optional to required.
 *
 * @see https://docs.gitlab.com/ee/ci/testing/code_quality.html#implement-a-custom-tool
 * @see https://github.com/codeclimate/platform/blob/master/spec/analyzers/SPEC.md#data-types
 *
 * @phpstan-type GitlabIssue array{
 *     type: 'issue',
 *     description: string,
 *     check_name: string,
 *     fingerprint: string,
 *     severity: 'minor',
 *     categories: array{'Style'},
 *     remediation_points?: int,
 *     location: array{
 *         path: string,
 *         lines: array{
 *             begin: int,
 *             end: int,
 *         },
 *     },
 * }
 */
final readonly class GitlabOutputFormatter implements OutputFormatterInterface
{
    public function __construct(
        private EasyCodingStandardStyle $easyCodingStandardStyle,
        private ExitCodeResolver $exitCodeResolver,
        private DiffParser $diffParser,
    ) {
    }

    public static function getName(): string
    {
        return 'gitlab';
    }

    public static function hasSupportForProgressBars(): bool
    {
        return false;
    }

    /**
     * @return ExitCode::*
     */
    public function report(ErrorAndDiffResult $errorAndDiffResult, Configuration $configuration): int
    {
        $output = $this->generateReport($errorAndDiffResult, $configuration);
        $this->easyCodingStandardStyle->writeln($output);
        return $this->exitCodeResolver->resolve($errorAndDiffResult, $configuration);
    }

    public function generateReport(ErrorAndDiffResult $errorAndDiffResult, Configuration $configuration): string
    {
        $reportedQualityIssues = (! $configuration->isFixer() && $configuration->shouldShowDiffs())
            ? merge(
                $this->generateIssuesForErrors(
                    $errorAndDiffResult->getErrors(),
                    $configuration->isReportingWithRealPath()
                ),
                $this->generateIssuesForFixes(
                    $errorAndDiffResult->getFileDiffs(),
                    $configuration->isReportingWithRealPath()
                ),
            )
            : $this->generateIssuesForErrors(
                $errorAndDiffResult->getErrors(),
                $configuration->isReportingWithRealPath()
            );

        return $this->encode($reportedQualityIssues);
    }

    /**
     * @param CodingStandardError[] $errors
     * @return GitlabIssue[]
     */
    private function generateIssuesForErrors(array $errors, bool $absoluteFilePath = false): array
    {
        return map(
            fn (CodingStandardError $codingStandardError): array => [
                'type' => 'issue',
                'description' => $codingStandardError->getMessage(),
                'check_name' => $codingStandardError->getCheckerClass(),
                'fingerprint' => $this->generateFingerprint(
                    $codingStandardError->getCheckerClass(),
                    $codingStandardError->getMessage(),
                    $codingStandardError->getRelativeFilePath(),
                ),
                'severity' => 'minor',
                'categories' => ['Style'],
                'location' => [
                    'path' => $absoluteFilePath ? $codingStandardError->getAbsoluteFilePath() ?? '' : $codingStandardError->getRelativeFilePath(),
                    'lines' => [
                        'begin' => $codingStandardError->getLine(),
                        'end' => $codingStandardError->getLine(),
                    ],
                ],
            ],
            $errors,
        );
    }

    /**
     * Reports each chunk of changes as a separate issue.
     *
     * @param FileDiff[] $diffs
     * @return GitlabIssue[]
     */
    private function generateIssuesForFixes(array $diffs, bool $absoluteFilePath = false): array
    {
        return merge(
            ...map(
                fn (FileDiff $fileDiff): array => map(
                    fn (Chunk $chunk): array => $this->generateIssueForChunk($fileDiff, $chunk, $absoluteFilePath),
                    $this->diffParser->parse($fileDiff->getDiff())[0]
                        ->chunks(),
                ),
                $diffs,
            ),
        );
    }

    /**
     * @return GitlabIssue
     */
    private function generateIssueForChunk(FileDiff $fileDiff, Chunk $chunk, bool $absoluteFilePath): array
    {
        $checkersAsFqcns = implode(',', $fileDiff->getAppliedCheckers());
        $checkersAsClasses = implode(', ', map(
            static fn (string $checker): string => preg_replace('/.*\\\/', '', $checker) ?? $checker,
            $fileDiff->getAppliedCheckers(),
        ));

        $message = 'Chunk has fixable errors: ' . $checkersAsClasses;
        $lineStart = $chunk->start();
        $lineEnd = $lineStart + $chunk->startRange() - 1;

        return [
            'type' => 'issue',
            'description' => $message,
            'check_name' => $checkersAsFqcns,
            'fingerprint' => $this->generateFingerprint(
                $checkersAsFqcns,
                $message,
                $fileDiff->getRelativeFilePath(),
                implode(
                    '\n',
                    map(static fn (Line $line): string => sprintf(
                        '%d:%s',
                        $line->type(),
                        $line->content()
                    ), $chunk->lines())
                ),
            ),
            'severity' => 'minor',
            'categories' => ['Style'],
            'remediation_points' => 50_000,
            'location' => [
                'path' => $absoluteFilePath ? $fileDiff->getAbsoluteFilePath() ?? '' : $fileDiff->getRelativeFilePath(),
                'lines' => [
                    'begin' => $lineStart,
                    'end' => $lineEnd,
                ],
            ],
        ];
    }

    /**
     * Generate a fingerprint for a given quality issue. This is used to
     * track the presence of an issue between runs, so it should be unique
     * and consistent for a given issue.
     *
     * Subsequently, changing the fingerprint or the data it uses is
     * _technically_ a breaking change. Users would see existing issues being
     * marked as "new" on the commit proceeding updating ECS.
     *
     * DO NOT include position information as salting, or every time
     * lines are added/removed the lines below it will be reported as
     * new errors.
     */
    private function generateFingerprint(
        string $checker,
        string $message,
        string $relativeFilePath,
        string $salt = '',
    ): string {
        // We implode to add a separator that cannot show up in PHP
        // class names or Linux file names and SHOULD  never show up in
        // messages. This guarantees the same fingerprint won't be generated
        // by accident, by the associative property of concatenation. As in:
        //
        // (ABC + ABC = ABCABC) == (ABCA + BC = ABCABC)
        // (ABC + \0 + ABC = ABC\0ABC) != (ABCA + \0 + BC = ABCA\0BC)
        return md5(implode("\0", [$checker, $message, $relativeFilePath, $salt]));
    }

    /**
     * @param GitlabIssue[] $lineItems
     */
    private function encode(array $lineItems): string
    {
        return json_encode(
            $lineItems,
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }
}
