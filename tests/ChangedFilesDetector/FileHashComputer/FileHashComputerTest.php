<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Tests\ChangedFilesDetector\FileHashComputer;

use Override;
use Symfony\Component\Filesystem\Filesystem;
use Symplify\EasyCodingStandard\Caching\FileHashComputer;
use Symplify\EasyCodingStandard\Testing\PHPUnit\AbstractTestCase;

final class FileHashComputerTest extends AbstractTestCase
{
    private const string INCLUDED_CONFIG_FILE = __DIR__ . '/Fixture/another-one.php';

    private FileHashComputer $fileHashComputer;

    private Filesystem $filesystem;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->fileHashComputer = $this->make(FileHashComputer::class);
        $this->filesystem = $this->make(Filesystem::class);
    }

    public function testInvalidateCacheOnConfigurationChange(): void
    {
        // A. create on another one with fixer
        $this->filesystem->copy(__DIR__ . '/Source/first_config.php', self::INCLUDED_CONFIG_FILE, true);

        $fileOneHash = $this->fileHashComputer->computeConfig(
            __DIR__ . '/Fixture/config-including-another-one.php'
        );

        // B. create on another one with no fixer
        $this->filesystem->copy(__DIR__ . '/Source/empty_config.php', self::INCLUDED_CONFIG_FILE, true);

        $fileTwoHash = $this->fileHashComputer->computeConfig(
            __DIR__ . '/Fixture/config-including-another-one.php'
        );

        $this->assertNotSame($fileOneHash, $fileTwoHash);

        $this->filesystem->remove(self::INCLUDED_CONFIG_FILE);
    }

    public function testPhpFileHash(): void
    {
        $fileOne = __DIR__ . '/Source/SomeScannedClass.php';
        $fileOneHash = $this->fileHashComputer->compute($fileOne);

        $expectedFileOneHasn = md5_file($fileOne);
        $this->assertSame($expectedFileOneHasn, $fileOneHash);

        $fileTwo = __DIR__ . '/Source/ChangedScannedClass.php';
        $fileTwoHash = $this->fileHashComputer->compute($fileTwo);

        $expectedFileTwoHash = md5_file($fileTwo);
        $this->assertSame($expectedFileTwoHash, $fileTwoHash);

        $this->assertNotSame($fileOneHash, $fileTwoHash);
    }
}
