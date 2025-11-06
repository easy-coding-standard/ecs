<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Configuration\EditorConfig;

/**
 * @see https://github.com/editorconfig/editorconfig/wiki/EditorConfig-Properties
 */
class EditorConfig
{
    /**
     * @readonly
     * @var string|null
     */
    public $indentStyle;
    /**
     * @readonly
     * @var string|null
     */
    public $endOfLine;
    /**
     * @readonly
     * @var bool|null
     */
    public $trimTrailingWhitespace;
    /**
     * @readonly
     * @var bool|null
     */
    public $insertFinalNewline;
    /**
     * @readonly
     * @var int|null
     */
    public $maxLineLength;
    /**
     * @readonly
     * @var string|null
     */
    public $quoteType;
    public function __construct(?string $indentStyle, ?string $endOfLine, ?bool $trimTrailingWhitespace, ?bool $insertFinalNewline, ?int $maxLineLength, ?string $quoteType)
    {
        $this->indentStyle = $indentStyle;
        $this->endOfLine = $endOfLine;
        $this->trimTrailingWhitespace = $trimTrailingWhitespace;
        $this->insertFinalNewline = $insertFinalNewline;
        $this->maxLineLength = $maxLineLength;
        $this->quoteType = $quoteType;
    }
}
