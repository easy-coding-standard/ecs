<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\Fixer\Commenting;

use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;
use Symplify\CodingStandard\DocBlock\UselessDocBlockCleaner;
use Symplify\CodingStandard\Fixer\AbstractSymplifyFixer;
use Symplify\CodingStandard\Fixer\Naming\ClassNameResolver;
use Symplify\CodingStandard\TokenRunner\Traverser\TokenReverser;
/**
 * @see \Symplify\CodingStandard\Tests\Fixer\Commenting\RemoveUselessDefaultCommentFixer\RemoveUselessDefaultCommentFixerTest
 */
final class RemoveUselessDefaultCommentFixer extends AbstractSymplifyFixer
{
    /**
     * @readonly
     * @var \Symplify\CodingStandard\DocBlock\UselessDocBlockCleaner
     */
    private $uselessDocBlockCleaner;
    /**
     * @readonly
     * @var \Symplify\CodingStandard\TokenRunner\Traverser\TokenReverser
     */
    private $tokenReverser;
    /**
     * @readonly
     * @var \Symplify\CodingStandard\Fixer\Naming\ClassNameResolver
     */
    private $classNameResolver;
    /**
     * @var string
     */
    private const ERROR_MESSAGE = 'Remove useless PHPStorm-generated @todo comments, redundant "Class XY" or "gets service" comments etc.';
    public function __construct(UselessDocBlockCleaner $uselessDocBlockCleaner, TokenReverser $tokenReverser, ClassNameResolver $classNameResolver)
    {
        $this->uselessDocBlockCleaner = $uselessDocBlockCleaner;
        $this->tokenReverser = $tokenReverser;
        $this->classNameResolver = $classNameResolver;
    }
    public function getDefinition() : FixerDefinitionInterface
    {
        return new FixerDefinition(self::ERROR_MESSAGE, []);
    }
    /**
     * @param Tokens<Token> $tokens
     */
    public function isCandidate(Tokens $tokens) : bool
    {
        return $tokens->isAnyTokenKindsFound([\T_DOC_COMMENT, \T_COMMENT]);
    }
    public function getPriority() : int
    {
        /** must run before @see \PhpCsFixer\Fixer\Basic\BracesFixer to cleanup spaces */
        return 40;
    }
    /**
     * @param Tokens<Token> $tokens
     */
    public function fix(SplFileInfo $fileInfo, Tokens $tokens) : void
    {
        $reversedTokens = $this->tokenReverser->reverse($tokens);
        foreach ($reversedTokens as $index => $token) {
            if (!$token->isGivenKind([\T_DOC_COMMENT, \T_COMMENT])) {
                continue;
            }
            $classLikeName = $this->classNameResolver->resolveClassName($fileInfo, $tokens);
            $originalContent = $token->getContent();
            $cleanedDocContent = $this->uselessDocBlockCleaner->clearDocTokenContent($token, $classLikeName);
            if ($cleanedDocContent === '') {
                // remove token
                $tokens->clearTokenAndMergeSurroundingWhitespace($index);
            } elseif ($cleanedDocContent !== $originalContent) {
                // update in case of other contents
                $tokens[$index] = new Token([\T_DOC_COMMENT, $cleanedDocContent]);
            }
        }
    }
}
