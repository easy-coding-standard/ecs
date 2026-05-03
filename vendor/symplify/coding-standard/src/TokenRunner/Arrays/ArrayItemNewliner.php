<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\TokenRunner\Arrays;

use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\WhitespacesFixerConfig;
use Symplify\CodingStandard\TokenRunner\Analyzer\FixerAnalyzer\ArrayAnalyzer;
use Symplify\CodingStandard\TokenRunner\ValueObject\BlockInfo;
final class ArrayItemNewliner
{
    /**
     * @readonly
     * @var \Symplify\CodingStandard\TokenRunner\Analyzer\FixerAnalyzer\ArrayAnalyzer
     */
    private $arrayAnalyzer;
    /**
     * @readonly
     * @var \PhpCsFixer\WhitespacesFixerConfig
     */
    private $whitespacesFixerConfig;
    public function __construct(ArrayAnalyzer $arrayAnalyzer, WhitespacesFixerConfig $whitespacesFixerConfig)
    {
        $this->arrayAnalyzer = $arrayAnalyzer;
        $this->whitespacesFixerConfig = $whitespacesFixerConfig;
    }
    /**
     * @param Tokens<Token> $tokens
     */
    public function fixArrayOpener(Tokens $tokens, BlockInfo $blockInfo) : void
    {
        $this->arrayAnalyzer->traverseArrayWithoutNesting($tokens, $blockInfo, function (Token $token, int $position, Tokens $tokens) : void {
            if ($token->getContent() !== ',') {
                return;
            }
            $nextTokenPosition = $position + 1;
            $nextToken = $tokens[$nextTokenPosition] ?? null;
            if (!$nextToken instanceof Token) {
                return;
            }
            if (\strpos($nextToken->getContent(), "\n") !== \false) {
                return;
            }
            $lookaheadPosition = $tokens->getNonWhitespaceSibling($position, 1, " \t\r\x00\v");
            if ($lookaheadPosition !== null && $tokens[$lookaheadPosition]->isGivenKind(\T_COMMENT)) {
                return;
            }
            if ($nextToken->getContent() === '{') {
                return;
            }
            $tokens->ensureWhitespaceAtIndex($nextTokenPosition, 0, $this->whitespacesFixerConfig->getLineEnding());
        });
    }
}
