<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Tests\ChangedFilesDetector\ChangedFilesDetector;

use Override;
use Symplify\EasyCodingStandard\Caching\ChangedFilesDetector;
use Symplify\EasyCodingStandard\Testing\PHPUnit\AbstractTestCase;

final class ChangedFilesDetectorTest extends AbstractTestCase
{
    private string $filePath;

    private ChangedFilesDetector $changedFilesDetector;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->filePath = __DIR__ . '/Source/OneClass.php';

        $this->changedFilesDetector = $this->make(ChangedFilesDetector::class);
        $this->changedFilesDetector->changeConfigurationFile(__DIR__ . '/Source/easy-coding-standard.php');
    }

    public function testAddFile(): void
    {
        $this->assertFileHasChanged($this->filePath);
        $this->assertFileHasChanged($this->filePath);
    }

    public function testHasFileChanged(): void
    {
        $this->changedFilesDetector->addFilePath($this->filePath);

        $this->assertFileHasNotChanged($this->filePath);
    }

    public function testInvalidateCacheOnConfigurationChange(): void
    {
        $this->changedFilesDetector->addFilePath($this->filePath);
        $this->assertFileHasNotChanged($this->filePath);

        $this->changedFilesDetector->changeConfigurationFile(__DIR__ . '/Source/another-configuration.php');

        $this->assertFileHasChanged($this->filePath);
    }

    private function assertFileHasChanged(string $filePath): void
    {
        $this->assertTrue($this->changedFilesDetector->hasFileChanged($filePath));
    }

    private function assertFileHasNotChanged(string $filePath): void
    {
        $this->assertFalse($this->changedFilesDetector->hasFileChanged($filePath));
    }
}
