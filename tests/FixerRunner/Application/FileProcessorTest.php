<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Tests\FixerRunner\Application;

use Override;
use PhpCsFixer\Fixer\ArrayNotation\NoTrailingCommaInSinglelineArrayFixer;
use PhpCsFixer\Fixer\Basic\EncodingFixer;
use PhpCsFixer\Fixer\PhpTag\FullOpeningTagFixer;
use Symplify\EasyCodingStandard\FixerRunner\Application\FixerFileProcessor;
use Symplify\EasyCodingStandard\Testing\PHPUnit\AbstractTestCase;

final class FileProcessorTest extends AbstractTestCase
{
    private FixerFileProcessor $fixerFileProcessor;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->createContainerWithConfigs([__DIR__ . '/Source/easy-coding-standard.php']);
        $this->fixerFileProcessor = $this->make(FixerFileProcessor::class);
    }

    public function testGetSortedCheckers(): void
    {
        $checkers = $this->fixerFileProcessor->getCheckers();

        $this->assertCount(3, $this->fixerFileProcessor->getCheckers());

        $this->assertInstanceOf(EncodingFixer::class, $checkers[0]);
        $this->assertInstanceOf(FullOpeningTagFixer::class, $checkers[1]);
        $this->assertInstanceOf(NoTrailingCommaInSinglelineArrayFixer::class, $checkers[2]);
    }
}
