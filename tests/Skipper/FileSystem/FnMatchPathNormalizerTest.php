<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Tests\Skipper\FileSystem;

use Iterator;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Symplify\EasyCodingStandard\Skipper\FileSystem\FnMatchPathNormalizer;
use Symplify\EasyCodingStandard\Testing\PHPUnit\AbstractTestCase;

final class FnMatchPathNormalizerTest extends AbstractTestCase
{
    private FnMatchPathNormalizer $fnMatchPathNormalizer;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->fnMatchPathNormalizer = $this->make(FnMatchPathNormalizer::class);
    }

    #[DataProvider('providePaths')]
    public function testPaths(string $path, string $expectedNormalizedPath): void
    {
        $normalizedPath = $this->fnMatchPathNormalizer->normalizeForFnmatch($path);
        $this->assertSame($expectedNormalizedPath, $normalizedPath);
    }

    public static function providePaths(): Iterator
    {
        yield ['path/with/no/asterisk', 'path/with/no/asterisk'];
        yield ['*path/with/asterisk/begin', '*path/with/asterisk/begin*'];
        yield ['path/with/asterisk/end*', '*path/with/asterisk/end*'];
        yield ['*path/with/asterisk/begin/and/end*', '*path/with/asterisk/begin/and/end*'];
        yield [__DIR__ . '/Fixture/path/with/../in/it', __DIR__ . '/Fixture/path/in/it'];
        yield [__DIR__ . '/Fixture/path/with/../../in/it', __DIR__ . '/Fixture/in/it'];
    }
}
