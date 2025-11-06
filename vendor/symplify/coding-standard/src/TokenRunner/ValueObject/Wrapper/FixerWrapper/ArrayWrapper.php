<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\TokenRunner\ValueObject\Wrapper\FixerWrapper;

use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use Symplify\CodingStandard\TokenRunner\Analyzer\FixerAnalyzer\TokenSkipper;
use Symplify\CodingStandard\TokenRunner\ValueObject\BlockInfo;
final class ArrayWrapper
{
    /**
     * @var Tokens<Token>
     * @readonly
     */
    private $tokens;
    /**
     * @readonly
     * @var \Symplify\CodingStandard\TokenRunner\ValueObject\BlockInfo
     */
    private $blockInfo;
    /**
     * @readonly
     * @var \Symplify\CodingStandard\TokenRunner\Analyzer\FixerAnalyzer\TokenSkipper
     */
    private $tokenSkipper;
    /**
     * @var int[]
     */
    private const ARRAY_OPEN_TOKENS = [\T_ARRAY, CT::T_ARRAY_SQUARE_BRACE_OPEN];
    /**
     * @param Tokens<Token> $tokens
     */
    public function __construct(Tokens $tokens, BlockInfo $blockInfo, TokenSkipper $tokenSkipper)
    {
        $this->tokens = $tokens;
        $this->blockInfo = $blockInfo;
        $this->tokenSkipper = $tokenSkipper;
    }
    public function isAssociativeArray() : bool
    {
        for ($i = $this->blockInfo->getStart() + 1; $i <= $this->blockInfo->getEnd() - 1; ++$i) {
            $i = $this->tokenSkipper->skipBlocks($this->tokens, $i);
            $token = $this->tokens[$i];
            if ($token->isGivenKind(\T_DOUBLE_ARROW)) {
                return \true;
            }
        }
        return \false;
    }
    public function getItemCount() : int
    {
        $itemCount = 0;
        for ($i = $this->blockInfo->getEnd() - 1; $i >= $this->blockInfo->getStart(); --$i) {
            $i = $this->tokenSkipper->skipBlocksReversed($this->tokens, $i);
            $token = $this->tokens[$i];
            if ($token->isGivenKind(\T_DOUBLE_ARROW)) {
                ++$itemCount;
            }
        }
        return $itemCount;
    }
    public function isFirstItemArray() : bool
    {
        for ($i = $this->blockInfo->getEnd() - 1; $i >= $this->blockInfo->getStart(); --$i) {
            $i = $this->tokenSkipper->skipBlocksReversed($this->tokens, $i);
            /** @var Token $token */
            $token = $this->tokens[$i];
            if ($token->isGivenKind(\T_DOUBLE_ARROW)) {
                $nextTokenAfterArrowPosition = $this->tokens->getNextNonWhitespace($i);
                if ($nextTokenAfterArrowPosition === null) {
                    return \false;
                }
                /** @var Token $nextToken */
                $nextToken = $this->tokens[$nextTokenAfterArrowPosition];
                return $nextToken->isGivenKind(self::ARRAY_OPEN_TOKENS);
            }
        }
        return \false;
    }
}
