<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\TokenRunner\Whitespace;

use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\WhitespacesFixerConfig;
use Symplify\CodingStandard\TokenRunner\Analyzer\FixerAnalyzer\IndentDetector;
final class IndentResolver
{
    /**
     * @readonly
     * @var \Symplify\CodingStandard\TokenRunner\Analyzer\FixerAnalyzer\IndentDetector
     */
    private $indentDetector;
    /**
     * @readonly
     * @var \PhpCsFixer\WhitespacesFixerConfig
     */
    private $whitespacesFixerConfig;
    public function __construct(IndentDetector $indentDetector, WhitespacesFixerConfig $whitespacesFixerConfig)
    {
        $this->indentDetector = $indentDetector;
        $this->whitespacesFixerConfig = $whitespacesFixerConfig;
    }
    /**
     * @param Tokens<Token> $tokens
     */
    public function resolveClosingBracketNewlineWhitespace(Tokens $tokens, int $startIndex) : string
    {
        $indentLevel = $this->indentDetector->detectOnPosition($tokens, $startIndex);
        return $this->whitespacesFixerConfig->getLineEnding() . \str_repeat($this->whitespacesFixerConfig->getIndent(), $indentLevel);
    }
    /**
     * @param Tokens<Token> $tokens
     */
    public function resolveCurrentNewlineIndentWhitespace(Tokens $tokens, int $index) : string
    {
        $indentLevel = $this->indentDetector->detectOnPosition($tokens, $index);
        $indentWhitespace = \str_repeat($this->whitespacesFixerConfig->getIndent(), $indentLevel);
        return $this->whitespacesFixerConfig->getLineEnding() . $indentWhitespace;
    }
    /**
     * @param Tokens<Token> $tokens
     */
    public function resolveNewlineIndentWhitespace(Tokens $tokens, int $index) : string
    {
        $indentWhitespace = $this->resolveIndentWhitespace($tokens, $index);
        return $this->whitespacesFixerConfig->getLineEnding() . $indentWhitespace;
    }
    /**
     * @param Tokens<Token> $tokens
     */
    private function resolveIndentWhitespace(Tokens $tokens, int $index) : string
    {
        $indentLevel = $this->indentDetector->detectOnPosition($tokens, $index);
        return \str_repeat($this->whitespacesFixerConfig->getIndent(), $indentLevel + 1);
    }
}
