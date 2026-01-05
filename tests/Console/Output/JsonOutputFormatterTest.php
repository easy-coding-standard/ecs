<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Tests\Console\Output;

use Override;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Console\Formatter\ColorConsoleDiffFormatter;
use Symplify\EasyCodingStandard\Console\Output\JsonOutputFormatter;
use Symplify\EasyCodingStandard\FileSystem\StaticRelativeFilePathHelper;
use Symplify\EasyCodingStandard\Testing\PHPUnit\AbstractTestCase;
use Symplify\EasyCodingStandard\ValueObject\Error\ErrorAndDiffResult;
use Symplify\EasyCodingStandard\ValueObject\Error\FileDiff;

final class JsonOutputFormatterTest extends AbstractTestCase
{
    private JsonOutputFormatter $jsonOutputFormatter;

    private ColorConsoleDiffFormatter $colorConsoleDiffFormatter;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->jsonOutputFormatter = $this->make(JsonOutputFormatter::class);
        $this->colorConsoleDiffFormatter = $this->make(ColorConsoleDiffFormatter::class);
    }

    public function test(): void
    {
        $relativeFilePath = StaticRelativeFilePathHelper::resolveFromCwd(__DIR__ . '/Source/RandomFile.php');

        $fileDiffs = [];

        $diff = 'some diff';
        $fileDiffs[] = new FileDiff(
            $relativeFilePath,
            $diff,
            $this->colorConsoleDiffFormatter->format($diff),
            [LineLengthFixer::class]
        );

        $diff = 'some other diff';
        $fileDiffs[] = new FileDiff(
            $relativeFilePath,
            $diff,
            $this->colorConsoleDiffFormatter->format($diff),
            [LineLengthFixer::class]
        );

        $errorAndDiffResult = new ErrorAndDiffResult([], $fileDiffs, []);

        $jsonContent = $this->jsonOutputFormatter->createJsonContent($errorAndDiffResult);
        $this->assertStringMatchesFormatFile(__DIR__ . '/Fixture/expected_json_output.json', $jsonContent . PHP_EOL);
    }
}
