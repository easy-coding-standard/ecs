<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Tests\Console\Output;

use Override;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff;
use PhpCsFixer\Differ\DifferInterface;
use PHPUnit\Framework\Attributes\Depends;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Console\Formatter\ColorConsoleDiffFormatter;
use Symplify\EasyCodingStandard\Console\Output\GitlabOutputFormatter;
use Symplify\EasyCodingStandard\FileSystem\StaticRelativeFilePathHelper;
use Symplify\EasyCodingStandard\SniffRunner\ValueObject\Error\CodingStandardError;
use Symplify\EasyCodingStandard\Testing\PHPUnit\AbstractTestCase;
use Symplify\EasyCodingStandard\ValueObject\Configuration;
use Symplify\EasyCodingStandard\ValueObject\Error\ErrorAndDiffResult;
use Symplify\EasyCodingStandard\ValueObject\Error\FileDiff;

final class GitlabOutputFormatterTest extends AbstractTestCase
{
    private GitlabOutputFormatter $gitlabOutputFormatter;

    private ColorConsoleDiffFormatter $colorConsoleDiffFormatter;

    private DifferInterface $differ;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->gitlabOutputFormatter = $this->make(gitlabOutputFormatter::class);
        $this->colorConsoleDiffFormatter = $this->make(ColorConsoleDiffFormatter::class);
        $this->differ = $this->make(DifferInterface::class);
    }

    public function testGracefullyHandlesNoIssues(): void
    {
        $configuration = new Configuration();
        $filePathForExpectedOutput = $this->path('/Fixture/gitlab/no_issues.json');

        $errorAndDiffResult = new ErrorAndDiffResult([], [], []);

        $this->assertJsonStringEqualsJsonFile(
            $filePathForExpectedOutput,
            $this->gitlabOutputFormatter->generateReport($errorAndDiffResult, $configuration),
        );
    }

    public function testReportsErrorsInTheRightFormat(): void
    {
        $configuration = new Configuration();
        $filePathForExpectedOutput = $this->path('/Fixture/gitlab/only_errors.json');

        [$simulatedErrors] = $this->getMockedIssues();

        $errorAndDiffResult = new ErrorAndDiffResult($simulatedErrors, [], []);
        $output = $this->gitlabOutputFormatter->generateReport($errorAndDiffResult, $configuration);

        $this->assertJsonStringEqualsJsonFile($filePathForExpectedOutput, $output);
    }

    public function testReportsFixesInTheRightFormat(): void
    {
        $configuration = new Configuration();
        $filePathForChanges = $this->path('/Source/RandomFileWithEdits.php');
        $filePathForExpectedOutput = $this->path('/Fixture/gitlab/only_fixes.json');

        [$_, $simulatedFixes] = $this->getMockedIssues($filePathForChanges);

        $errorAndDiffResult = new ErrorAndDiffResult([], $simulatedFixes, []);
        $output = $this->gitlabOutputFormatter->generateReport($errorAndDiffResult, $configuration);

        $this->assertJsonStringEqualsJsonFile($filePathForExpectedOutput, $output);
    }

    public function testReportsErrorsAndFixesByDefault(): void
    {
        $configuration = new Configuration();
        $filePathForChanges = $this->path('/Source/RandomFileWithEdits.php');
        $filePathForExpectedOutput = $this->path('/Fixture/gitlab/errors_and_fixes.json');

        [$simulatedErrors, $simulatedFixes] = $this->getMockedIssues($filePathForChanges);

        $errorAndDiffResult = new ErrorAndDiffResult($simulatedErrors, $simulatedFixes, []);
        $output = $this->gitlabOutputFormatter->generateReport($errorAndDiffResult, $configuration);

        $this->assertJsonStringEqualsJsonFile($filePathForExpectedOutput, $output);
    }

    public function testReportsOnlyErrorsWithNoDiffsFlag(): void
    {
        $configuration = new Configuration(showDiffs: false);
        $filePathForChanges = $this->path('/Source/RandomFileWithEdits.php');
        $filePathForExpectedOutput = $this->path('/Fixture/gitlab/only_errors.json');

        [$simulatedErrors, $simulatedFixes] = $this->getMockedIssues($filePathForChanges);

        $errorAndDiffResult = new ErrorAndDiffResult($simulatedErrors, $simulatedFixes, []);
        $output = $this->gitlabOutputFormatter->generateReport($errorAndDiffResult, $configuration);

        $this->assertJsonStringEqualsJsonFile($filePathForExpectedOutput, $output);
    }

    public function testReportsOnlyErrorsWithFixFlag(): void
    {
        $configuration = new Configuration(isFixer: true);
        $filePathForChanges = $this->path('/Source/RandomFileWithEdits.php');
        $filePathForExpectedOutput = $this->path('/Fixture/gitlab/only_errors.json');

        [$simulatedErrors, $simulatedFixes] = $this->getMockedIssues($filePathForChanges);

        $errorAndDiffResult = new ErrorAndDiffResult($simulatedErrors, $simulatedFixes, []);
        $output = $this->gitlabOutputFormatter->generateReport($errorAndDiffResult, $configuration);

        $this->assertJsonStringEqualsJsonFile($filePathForExpectedOutput, $output);
    }

    #[Depends('testReportsFixesInTheRightFormat')]
    public function testIssueFingerpintsDoNotChangeFromSimpleLineOffsets(): void
    {
        $configuration = new Configuration();
        $filePathForOriginal = $this->path('/Source/RandomFileWithSimpleOffset.php');
        $mockedFilePathForOriginal = $this->path('/Source/RandomFile.php');
        $filePathForChanges = $this->path('/Source/RandomFileWithEditsAndSimpleOffset.php');
        $filePathForExpectedOutput = $this->path('/Fixture/gitlab/only_fixes_with_offset.json');

        $diff = $this->differ->diff(
            file_get_contents($filePathForOriginal) ?: 'ERROR 1',
            file_get_contents($filePathForChanges) ?: 'ERROR 2',
        );

        // We need to mock the filepath because it's used as fingerprint material.
        $simulatedFixes = [
            new FileDiff(
                $mockedFilePathForOriginal,
                $diff,
                $this->colorConsoleDiffFormatter->format($diff),
                [LineLengthFixer::class],
            ),
        ];

        $errorAndDiffResult = new ErrorAndDiffResult([], $simulatedFixes, []);
        $output = $this->gitlabOutputFormatter->generateReport($errorAndDiffResult, $configuration);

        $this->assertJsonStringEqualsJsonFile($filePathForExpectedOutput, $output);
    }

    private function path(string $path): string
    {
        return StaticRelativeFilePathHelper::resolveFromCwd(__DIR__ . $path);
    }

    /**
     * @return array{CodingStandardError[], FileDiff[]}
     */
    private function getMockedIssues(?string $filePathForChanges = null): array
    {
        $filePathForOriginal = $this->path('/Source/RandomFile.php');

        $simulatedErrors = [
            new CodingStandardError(3, 'This is a test', LineLengthSniff::class, $filePathForOriginal),
            new CodingStandardError(5, 'This is another test', LineLengthSniff::class, $filePathForOriginal),
        ];

        if ($filePathForChanges === null) {
            return [$simulatedErrors, []];
        }

        $diff = $this->differ->diff(
            file_get_contents($filePathForOriginal) ?: 'ERROR 1',
            file_get_contents($filePathForChanges) ?: 'ERROR 2',
        );

        $simulatedFixes = [
            new FileDiff(
                $filePathForOriginal,
                $diff,
                $this->colorConsoleDiffFormatter->format($diff),
                [LineLengthFixer::class],
            ),
        ];

        return [$simulatedErrors, $simulatedFixes];
    }
}
