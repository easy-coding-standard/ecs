<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\Fixer\ArrayNotation;

use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\WhitespacesFixerConfig;
use SplFileInfo;
use Symplify\CodingStandard\Enum\BlockBorderType;
use Symplify\CodingStandard\Fixer\AbstractSymplifyFixer;
use Symplify\CodingStandard\TokenRunner\Analyzer\FixerAnalyzer\ArrayAnalyzer;
use Symplify\CodingStandard\TokenRunner\Traverser\ArrayBlockInfoFinder;
use Symplify\CodingStandard\TokenRunner\ValueObject\TokenKinds;
use Symplify\CodingStandard\ValueObject\BlockInfoMetadata;
/**
 * @see \Symplify\CodingStandard\Tests\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer\ArrayOpenerAndCloserNewlineFixerTest
 */
final class ArrayOpenerAndCloserNewlineFixer extends AbstractSymplifyFixer
{
    /**
     * @readonly
     * @var \Symplify\CodingStandard\TokenRunner\Traverser\ArrayBlockInfoFinder
     */
    private $arrayBlockInfoFinder;
    /**
     * @readonly
     * @var \PhpCsFixer\WhitespacesFixerConfig
     */
    private $whitespacesFixerConfig;
    /**
     * @readonly
     * @var \Symplify\CodingStandard\TokenRunner\Analyzer\FixerAnalyzer\ArrayAnalyzer
     */
    private $arrayAnalyzer;
    /**
     * @var string
     */
    private const ERROR_MESSAGE = 'Indexed PHP array opener [ and closer ] must be on own line';
    public function __construct(ArrayBlockInfoFinder $arrayBlockInfoFinder, WhitespacesFixerConfig $whitespacesFixerConfig, ArrayAnalyzer $arrayAnalyzer)
    {
        $this->arrayBlockInfoFinder = $arrayBlockInfoFinder;
        $this->whitespacesFixerConfig = $whitespacesFixerConfig;
        $this->arrayAnalyzer = $arrayAnalyzer;
    }
    public function getDefinition() : FixerDefinitionInterface
    {
        return new FixerDefinition(self::ERROR_MESSAGE, []);
    }
    /**
     * Must run before
     *
     * @see \PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer::getPriority()
     */
    public function getPriority() : int
    {
        return 34;
    }
    /**
     * @param Tokens<Token> $tokens
     */
    public function isCandidate(Tokens $tokens) : bool
    {
        if (!$tokens->isAnyTokenKindsFound(TokenKinds::ARRAY_OPEN_TOKENS)) {
            return \false;
        }
        return $tokens->isTokenKindFound(\T_DOUBLE_ARROW);
    }
    /**
     * @param Tokens<Token> $tokens
     */
    public function fix(SplFileInfo $fileInfo, Tokens $tokens) : void
    {
        $blockInfos = $this->arrayBlockInfoFinder->findArrayOpenerBlockInfos($tokens);
        $blockInfoMetadatas = [];
        foreach ($blockInfos as $blockInfo) {
            $blockInfoMetadatas[$blockInfo->getStart()] = new BlockInfoMetadata(BlockBorderType::OPENER, $blockInfo);
            $blockInfoMetadatas[$blockInfo->getEnd()] = new BlockInfoMetadata(BlockBorderType::CLOSER, $blockInfo);
        }
        // sort from the highest position to the lowest, so we respect the changed tokens from bottom to the top convention
        \krsort($blockInfoMetadatas);
        foreach ($blockInfoMetadatas as $blockInfoMetadata) {
            $this->fixPositionAndType($blockInfoMetadata, $tokens);
        }
    }
    /**
     * @param Tokens<Token> $tokens
     */
    private function fixPositionAndType(BlockInfoMetadata $blockInfoMetadata, Tokens $tokens) : void
    {
        $blockInfo = $blockInfoMetadata->getBlockInfo();
        if ($this->isNextTokenAlsoArrayOpener($tokens, $blockInfo->getStart())) {
            return;
        }
        // no items
        $itemCount = $this->arrayAnalyzer->getItemCount($tokens, $blockInfo);
        if ($itemCount === 0) {
            return;
        }
        if (!$this->arrayAnalyzer->isIndexedList($tokens, $blockInfo)) {
            return;
        }
        // closer must run before the opener, as tokens as added by traversing up
        if ($blockInfoMetadata->getBlockType() === BlockBorderType::CLOSER) {
            $this->handleArrayCloser($tokens, $blockInfo->getEnd());
        } elseif ($blockInfoMetadata->getBlockType() === BlockBorderType::OPENER) {
            $this->handleArrayOpener($tokens, $blockInfo->getStart());
        }
    }
    /**
     * @param Tokens<Token> $tokens
     */
    private function isNextTokenAlsoArrayOpener(Tokens $tokens, int $index) : bool
    {
        $nextToken = $this->getNextMeaningfulToken($tokens, $index);
        if (!$nextToken instanceof Token) {
            return \false;
        }
        return $nextToken->isGivenKind(TokenKinds::ARRAY_OPEN_TOKENS);
    }
    /**
     * @param Tokens<Token> $tokens
     */
    private function handleArrayCloser(Tokens $tokens, int $arrayCloserPosition) : void
    {
        $preArrayCloserPosition = $arrayCloserPosition - 1;
        $previousCloserToken = $tokens[$preArrayCloserPosition] ?? null;
        if (!$previousCloserToken instanceof Token) {
            return;
        }
        // already whitespace
        if (\strpos($previousCloserToken->getContent(), "\n") !== \false) {
            return;
        }
        $tokens->ensureWhitespaceAtIndex($preArrayCloserPosition, 1, $this->whitespacesFixerConfig->getLineEnding());
    }
    /**
     * @param Tokens<Token> $tokens
     */
    private function handleArrayOpener(Tokens $tokens, int $arrayOpenerPosition) : void
    {
        $postArrayOpenerPosition = $arrayOpenerPosition + 1;
        $nextToken = $tokens[$postArrayOpenerPosition] ?? null;
        if (!$nextToken instanceof Token) {
            return;
        }
        // already is whitespace
        if (\strpos($nextToken->getContent(), "\n") !== \false) {
            return;
        }
        $tokens->ensureWhitespaceAtIndex($postArrayOpenerPosition, 0, $this->whitespacesFixerConfig->getLineEnding());
    }
    /**
     * @param Tokens<Token> $tokens
     */
    private function getNextMeaningfulToken(Tokens $tokens, int $index) : ?Token
    {
        $nextMeaningfulTokenPosition = $tokens->getNextMeaningfulToken($index);
        if ($nextMeaningfulTokenPosition === null) {
            return null;
        }
        return $tokens[$nextMeaningfulTokenPosition];
    }
}
