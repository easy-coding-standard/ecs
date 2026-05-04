<?php

declare (strict_types=1);
namespace ECSPrefix202605\Entropy\Console\Output;

use ECSPrefix202605\Entropy\Console\Enum\Color;
use ECSPrefix202605\Webmozart\Assert\Assert;
/**
 * @api used in many ways
 */
final class OutputPrinter
{
    /**
     * @readonly
     * @var \Entropy\Console\Output\OutputColorizer
     */
    private $outputColorizer;
    /**
     * @readonly
     * @var bool
     */
    private $isSilent;
    public function __construct(OutputColorizer $outputColorizer)
    {
        $this->outputColorizer = $outputColorizer;
        // avoid printing to stdout during unit tests
        $this->isSilent = defined('PHPUNIT_COMPOSER_INSTALL');
    }
    /**
     * Handle color background and foreground tags in the text
     * e.g. <fg=green>text</>, <bg=red>text</>
     */
    public function writeln(string $text, int $newlineCount = 0): void
    {
        if ($this->isSilent) {
            return;
        }
        $coloredText = $this->outputColorizer->colorize($text);
        fwrite(\STDOUT, $coloredText . \PHP_EOL);
        if ($newlineCount !== 0) {
            $this->newline($newlineCount);
        }
    }
    public function yellow(string $text): void
    {
        $colorizedText = $this->outputColorizer->color($text, 'yellow');
        $this->writeln($colorizedText);
    }
    public function green(string $text): void
    {
        $colorizedText = $this->outputColorizer->color($text, 'green');
        $this->writeln($colorizedText);
    }
    public function orangeBackground(string $text): void
    {
        $this->writeln($this->outputColorizer->background($text, Color::YELLOW));
    }
    public function greenBackground(string $text): void
    {
        $this->writeln($this->outputColorizer->background($text, Color::GREEN));
    }
    public function redBackground(string $text): void
    {
        $this->writeln($this->outputColorizer->background($text, Color::RED));
    }
    public function newline(int $count = 1): void
    {
        if ($this->isSilent) {
            return;
        }
        fwrite(\STDOUT, str_repeat(\PHP_EOL, $count));
    }
    /**
     * @param string[] $items
     */
    public function listing(array $items, string $bulletChar = '*'): void
    {
        Assert::allString($items);
        foreach ($items as $item) {
            $this->writeln(sprintf('%s %s', $bulletChar, $item));
        }
    }
    public function title(string $text): void
    {
        $this->newline();
        $this->yellow($text);
        $this->yellow(str_repeat('=', strlen($text)));
        $this->newline();
    }
}
