<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\TokenRunner\Transformer\FixerTransformer;

use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use Symplify\CodingStandard\TokenRunner\Enum\LineKind;
use Symplify\CodingStandard\TokenRunner\ValueObject\BlockInfo;
final class LineLengthTransformer
{
    /**
     * @readonly
     * @var \Symplify\CodingStandard\TokenRunner\Transformer\FixerTransformer\LineLengthResolver
     */
    private $lineLengthResolver;
    /**
     * @readonly
     * @var \Symplify\CodingStandard\TokenRunner\Transformer\FixerTransformer\TokensInliner
     */
    private $tokensInliner;
    /**
     * @readonly
     * @var \Symplify\CodingStandard\TokenRunner\Transformer\FixerTransformer\FirstLineLengthResolver
     */
    private $firstLineLengthResolver;
    /**
     * @readonly
     * @var \Symplify\CodingStandard\TokenRunner\Transformer\FixerTransformer\TokensNewliner
     */
    private $tokensNewliner;
    public function __construct(\Symplify\CodingStandard\TokenRunner\Transformer\FixerTransformer\LineLengthResolver $lineLengthResolver, \Symplify\CodingStandard\TokenRunner\Transformer\FixerTransformer\TokensInliner $tokensInliner, \Symplify\CodingStandard\TokenRunner\Transformer\FixerTransformer\FirstLineLengthResolver $firstLineLengthResolver, \Symplify\CodingStandard\TokenRunner\Transformer\FixerTransformer\TokensNewliner $tokensNewliner)
    {
        $this->lineLengthResolver = $lineLengthResolver;
        $this->tokensInliner = $tokensInliner;
        $this->firstLineLengthResolver = $firstLineLengthResolver;
        $this->tokensNewliner = $tokensNewliner;
    }
    /**
     * @param Tokens<Token> $tokens
     */
    public function fixStartPositionToEndPosition(BlockInfo $blockInfo, Tokens $tokens, int $lineLength, bool $breakLongLines, bool $inlineShortLine) : void
    {
        if ($this->hasPromotedProperty($tokens, $blockInfo)) {
            return;
        }
        $firstLineLength = $this->firstLineLengthResolver->resolveFromTokensAndStartPosition($tokens, $blockInfo);
        if ($firstLineLength > $lineLength && $breakLongLines) {
            $this->tokensNewliner->breakItems($blockInfo, $tokens, LineKind::CALLS);
            return;
        }
        $fullLineLength = $this->lineLengthResolver->getLengthFromStartEnd($tokens, $blockInfo);
        if ($fullLineLength > $lineLength) {
            return;
        }
        if (!$inlineShortLine) {
            return;
        }
        $this->tokensInliner->inlineItems($tokens, $blockInfo);
    }
    /**
     * @param Tokens<Token> $tokens
     */
    private function hasPromotedProperty(Tokens $tokens, BlockInfo $blockInfo) : bool
    {
        $resultByKind = $tokens->findGivenKind([CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC, CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED, CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE], $blockInfo->getStart(), $blockInfo->getEnd());
        return (bool) \array_filter($resultByKind);
    }
}
