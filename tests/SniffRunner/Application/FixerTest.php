<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Tests\SniffRunner\Application;

use Override;
use PHP_CodeSniffer\Fixer;
use Symplify\EasyCodingStandard\SniffRunner\File\FileFactory;
use Symplify\EasyCodingStandard\SniffRunner\ValueObject\File;
use Symplify\EasyCodingStandard\Testing\PHPUnit\AbstractTestCase;

final class FixerTest extends AbstractTestCase
{
    private Fixer $fixer;

    private File $file;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $fileFactory = $this->make(FileFactory::class);

        $this->file = $fileFactory->createFromFile(__DIR__ . '/FixerSource/SomeFile.php');
        $this->fixer = $this->make(Fixer::class);
    }

    public function testStartFile(): void
    {
        $this->assertSame('', $this->fixer->getContents());

        $this->file->parse();
        $this->fixer->startFile($this->file);

        $this->assertStringEqualsFile(__DIR__ . '/FixerSource/SomeFile.php', $this->fixer->getContents());
    }

    public function testTokenContent(): void
    {
        $this->file->parse();
        $this->fixer->startFile($this->file);

        // Tokenization of identifier names changed in PHPCS from 3.x to 4.0
        // https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki/Version-4.0-Developer-Upgrade-Guide#namespaced-names
        $token = $this->fixer->getTokenContent(14);
        $this->assertSame('Symplify\EasyCodingStandard\Tests\SniffRunner\Application\FixerSource', $token);

        $this->fixer->replaceToken(14, '_');
        $token = $this->fixer->getTokenContent(14);
        $this->assertSame('_', $token);

        $this->assertStringNotEqualsFile(__DIR__ . '/FixerSource/SomeFile.php', $this->fixer->getContents());
    }

    public function testAddContent(): void
    {
        $this->file->parse();
        $this->fixer->startFile($this->file);
        $this->fixer->beginChangeSet();

        $this->fixer->addContentBefore(14, 'A');

        $token = $this->fixer->getTokenContent(14);
        $this->assertSame('ASymplify\EasyCodingStandard\Tests\SniffRunner\Application\FixerSource', $token);

        $this->fixer->addContent(14, 'B');
        $token = $this->fixer->getTokenContent(14);
        $this->assertSame('ASymplify\EasyCodingStandard\Tests\SniffRunner\Application\FixerSourceB', $token);
    }

    public function testChangesets(): void
    {
        $this->file->parse();
        $this->fixer->startFile($this->file);
        $this->fixer->beginChangeSet();

        $tokenContent = $this->fixer->getTokenContent(14);
        $this->assertSame('Symplify\EasyCodingStandard\Tests\SniffRunner\Application\FixerSource', $tokenContent);

        $this->fixer->addContentBefore(14, 'A');
        $tokenContent = $this->fixer->getTokenContent(14);
        $this->assertSame('ASymplify\EasyCodingStandard\Tests\SniffRunner\Application\FixerSource', $tokenContent);

        // during the changeset, you are free to modify current token as you wish...
        $this->fixer->addContent(14, 'B');
        $tokenContent = $this->fixer->getTokenContent(14);
        $this->assertSame('ASymplify\EasyCodingStandard\Tests\SniffRunner\Application\FixerSourceB', $tokenContent);

        // you can also rollback the changes...
        $this->fixer->rollbackChangeset();
        $tokenContent = $this->fixer->getTokenContent(14);
        $this->assertSame('Symplify\EasyCodingStandard\Tests\SniffRunner\Application\FixerSource', $tokenContent);

        $this->fixer->addContent(14, 'B');
        $this->fixer->endChangeSet();

        $tokenContent = $this->fixer->getTokenContent(14);
        $this->assertSame('Symplify\EasyCodingStandard\Tests\SniffRunner\Application\FixerSourceB', $tokenContent);

        // ...that stops being the case after changeset is committed
        $this->fixer->addContent(14, 'C');
        $tokenContent = $this->fixer->getTokenContent(14);
        $this->assertSame('Symplify\EasyCodingStandard\Tests\SniffRunner\Application\FixerSourceB', $tokenContent);
    }

    public function testAddNewline(): void
    {
        $this->file->parse();
        $this->fixer->startFile($this->file);
        $this->fixer->beginChangeSet();

        $token = $this->fixer->getTokenContent(14);
        $this->assertSame('Symplify\EasyCodingStandard\Tests\SniffRunner\Application\FixerSource', $token);

        $this->fixer->addNewline(14);
        $token = $this->fixer->getTokenContent(14);
        $this->assertSame('Symplify\EasyCodingStandard\Tests\SniffRunner\Application\FixerSource' . PHP_EOL, $token);

        $this->fixer->addNewlineBefore(14);
        $token = $this->fixer->getTokenContent(14);
        $this->assertSame(
            PHP_EOL . 'Symplify\EasyCodingStandard\Tests\SniffRunner\Application\FixerSource' . PHP_EOL,
            $token
        );
    }

    public function testSubstrToken(): void
    {
        $this->file->parse();
        $this->fixer->startFile($this->file);
        $this->fixer->beginChangeSet();

        $token = $this->fixer->getTokenContent(14);
        $this->assertSame('Symplify\EasyCodingStandard\Tests\SniffRunner\Application\FixerSource', $token);

        $this->fixer->substrToken(14, 0, 4);
        $token = $this->fixer->getTokenContent(14);
        $this->assertSame('Symp', $token);

        $this->fixer->substrToken(14, 3);
        $token = $this->fixer->getTokenContent(14);
        $this->assertSame('p', $token);

        $this->fixer->substrToken(14, 3, 0);
        $token = $this->fixer->getTokenContent(14);
        $this->assertSame('', $token);
    }
}
