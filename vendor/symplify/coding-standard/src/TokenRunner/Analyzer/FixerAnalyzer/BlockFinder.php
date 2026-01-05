<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\TokenRunner\Analyzer\FixerAnalyzer;

use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use Symplify\CodingStandard\TokenRunner\Exception\MissingImplementationException;
use Symplify\CodingStandard\TokenRunner\ValueObject\BlockInfo;
use Throwable;
final class BlockFinder
{
    /**
     * @var array<string, int>
     */
    private const CONTENT_TO_BLOCK_TYPE = ['(' => Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, ')' => Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, '[' => Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE, ']' => Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE, '{' => Tokens::BLOCK_TYPE_CURLY_BRACE, '}' => Tokens::BLOCK_TYPE_CURLY_BRACE, '#[' => Tokens::BLOCK_TYPE_ATTRIBUTE];
    /**
     * @var string[]
     */
    private const START_EDGES = ['(', '[', '{'];
    /**
     * Accepts position to both start and end token, e.g. (, ), [, ], {, } also to: "array"(, "function" ...(, "use"(,
     * "new" ...(
     *
     * @param Tokens<Token> $tokens
     */
    public function findInTokensByEdge(Tokens $tokens, int $position) : ?BlockInfo
    {
        $token = $tokens[$position];
        if ($token->isGivenKind(\T_ATTRIBUTE)) {
            return $this->createAttributeBlockInfo($tokens, $position);
        }
        // shift "array" to "(", event its position
        if ($token->isGivenKind(\T_ARRAY)) {
            $position = $tokens->getNextMeaningfulToken($position);
            if ($position === null) {
                return null;
            }
            /** @var Token $token */
            $token = $tokens[$position];
        }
        if ($token->isGivenKind([\T_FUNCTION, CT::T_USE_LAMBDA, \T_NEW])) {
            $position = $tokens->getNextTokenOfKind($position, ['(', ';']);
            if ($position === null) {
                return null;
            }
            /** @var Token $token */
            $token = $tokens[$position];
            // end of line was sooner => has no block
            if ($token->equals(';')) {
                return null;
            }
            if ($token->equals('(')) {
                $closingPosition = $tokens->getNextMeaningfulToken($position);
                if ($closingPosition !== null) {
                    $closingToken = $tokens[$closingPosition];
                    if ($closingToken->equals(')')) {
                        // function has no arguments
                        return null;
                    }
                }
            }
        }
        $blockType = $this->getBlockTypeByToken($token);
        return $this->createBlockInfo($token, $position, $tokens, $blockType);
    }
    /**
     * @param Tokens<Token> $tokens
     */
    public function findInTokensByPositionAndContent(Tokens $tokens, int $position, string $content) : ?BlockInfo
    {
        $blockStart = $tokens->getNextTokenOfKind($position, [$content]);
        if ($blockStart === null) {
            return null;
        }
        $blockType = $this->getBlockTypeByContent($content);
        return new BlockInfo($blockStart, $tokens->findBlockEnd($blockType, $blockStart));
    }
    /**
     * @return Tokens::BLOCK_TYPE_*
     */
    private function getBlockTypeByContent(string $content) : int
    {
        if (isset(self::CONTENT_TO_BLOCK_TYPE[$content])) {
            return self::CONTENT_TO_BLOCK_TYPE[$content];
        }
        throw new MissingImplementationException(\sprintf('Implementation is missing for "%s" in "%s". Just add it to "%s" property with proper block type', $content, __METHOD__, '$contentToBlockType'));
    }
    /**
     * @return Tokens::BLOCK_TYPE_*
     */
    private function getBlockTypeByToken(Token $token) : int
    {
        if ($token->isArray()) {
            if (\in_array($token->getContent(), ['[', ']'], \true)) {
                return Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE;
            }
            return Tokens::BLOCK_TYPE_ARRAY_INDEX_CURLY_BRACE;
        }
        return $this->getBlockTypeByContent($token->getContent());
    }
    /**
     * @param Tokens<Token> $tokens
     * @param Tokens::BLOCK_TYPE_* $blockType
     */
    private function createBlockInfo(Token $token, int $position, Tokens $tokens, int $blockType) : ?BlockInfo
    {
        try {
            if (\in_array($token->getContent(), self::START_EDGES, \true)) {
                $blockStart = $position;
                $blockEnd = $tokens->findBlockEnd($blockType, $blockStart);
            } else {
                $blockEnd = $position;
                $blockStart = $tokens->findBlockStart($blockType, $blockEnd);
            }
        } catch (Throwable $exception) {
            // intentionally, no edge found
            return null;
        }
        return new BlockInfo($blockStart, $blockEnd);
    }
    /**
     * @param Tokens<Token> $tokens
     */
    private function createAttributeBlockInfo(Tokens $tokens, int $position) : ?BlockInfo
    {
        // find optional attribute opener, "#[Some()]"
        $openerPosition = $tokens->getNextTokenOfKind($position, ['(']);
        if (\is_int($openerPosition)) {
            $position = $openerPosition;
        }
        /** @var Token $token */
        $token = $tokens[$position];
        return $this->createBlockInfo($token, $position, $tokens, Tokens::BLOCK_TYPE_ATTRIBUTE);
    }
}
