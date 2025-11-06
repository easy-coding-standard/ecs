<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\TokenRunner\Transformer\FixerTransformer;

use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use Symplify\CodingStandard\TokenRunner\Analyzer\FixerAnalyzer\TokenSkipper;
use Symplify\CodingStandard\TokenRunner\ValueObject\BlockInfo;
final class TokensInliner
{
    /**
     * @readonly
     * @var \Symplify\CodingStandard\TokenRunner\Analyzer\FixerAnalyzer\TokenSkipper
     */
    private $tokenSkipper;
    public function __construct(TokenSkipper $tokenSkipper)
    {
        $this->tokenSkipper = $tokenSkipper;
    }
    /**
     * @param Tokens<Token> $tokens
     */
    public function inlineItems(Tokens $tokens, BlockInfo $blockInfo) : void
    {
        // replace line feeds with " "
        for ($i = $blockInfo->getStart() + 1; $i < $blockInfo->getEnd(); ++$i) {
            /** @var Token $currentToken */
            $currentToken = $tokens[$i];
            $i = $this->tokenSkipper->skipBlocks($tokens, $i);
            if (!$currentToken->isGivenKind(\T_WHITESPACE)) {
                continue;
            }
            /** @var Token $previousToken */
            $previousToken = $tokens[$i - 1];
            /** @var Token $nextToken */
            $nextToken = $tokens[$i + 1];
            // do not clear before *doc end, removing spaces breaks stuff
            if ($previousToken->isGivenKind([\T_START_HEREDOC, \T_END_HEREDOC])) {
                continue;
            }
            // clear space after opening and before closing bracket
            if ($this->isBlockStartOrEnd($previousToken, $nextToken)) {
                $tokens->clearAt($i);
                continue;
            }
            $tokens[$i] = new Token([\T_WHITESPACE, ' ']);
        }
    }
    private function isBlockStartOrEnd(Token $previousToken, Token $nextToken) : bool
    {
        if (\in_array($previousToken->getContent(), ['(', '['], \true)) {
            return \true;
        }
        return \in_array($nextToken->getContent(), [')', ']'], \true);
    }
}
