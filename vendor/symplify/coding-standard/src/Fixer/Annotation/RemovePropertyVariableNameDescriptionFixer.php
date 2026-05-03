<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\Fixer\Annotation;

use ECSPrefix202605\Nette\Utils\Strings;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;
use Symplify\CodingStandard\Fixer\AbstractSymplifyFixer;
use Symplify\CodingStandard\Fixer\Naming\PropertyNameResolver;
use Symplify\CodingStandard\TokenRunner\Traverser\TokenReverser;
/**
 * @see \Symplify\CodingStandard\Tests\Fixer\Annotation\RemovePropertyVariableNameDescriptionFixer\RemovePropertyVariableNameDescriptionFixerTest
 */
final class RemovePropertyVariableNameDescriptionFixer extends AbstractSymplifyFixer
{
    /**
     * @readonly
     * @var \Symplify\CodingStandard\TokenRunner\Traverser\TokenReverser
     */
    private $tokenReverser;
    /**
     * @var string
     */
    private const ERROR_MESSAGE = 'Remove useless "$variable" from @var tag';
    /**
     * @var string
     * @see https://regex101.com/r/2PxeKF/1
     */
    private const VAR_REGEX = '#@(?:psalm-|phpstan-)?var#';
    /**
     * @readonly
     * @var \Symplify\CodingStandard\Fixer\Naming\PropertyNameResolver
     */
    private $propertyNameResolver;
    public function __construct(TokenReverser $tokenReverser)
    {
        $this->tokenReverser = $tokenReverser;
        $this->propertyNameResolver = new PropertyNameResolver();
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
        if (!$tokens->isTokenKindFound(\T_VARIABLE)) {
            return \false;
        }
        return $tokens->isAnyTokenKindsFound([\T_DOC_COMMENT, \T_COMMENT]);
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
            $propertyName = $this->propertyNameResolver->resolve($tokens, $index);
            if ($propertyName === null) {
                continue;
            }
            // skip if not setter or getter
            $originalDocContent = $token->getContent();
            \preg_match_all(self::VAR_REGEX, $originalDocContent, $matches);
            if (\count($matches[0]) !== 1) {
                continue;
            }
            $hasChanged = \false;
            $docblockLines = \explode("\n", $originalDocContent);
            foreach ($docblockLines as $key => $docblockLine) {
                if (\substr_compare($docblockLine, ' ' . $propertyName, -\strlen(' ' . $propertyName)) !== 0) {
                    continue;
                }
                if (!\preg_match(self::VAR_REGEX, $docblockLine)) {
                    continue;
                }
                // remove last x characters
                $docblockLine = Strings::substring($docblockLine, 0, -\strlen(' ' . $propertyName));
                $hasChanged = \true;
                $docblockLines[$key] = \rtrim($docblockLine);
            }
            if (!$hasChanged) {
                continue;
            }
            $tokens[$index] = new Token([\T_DOC_COMMENT, \implode("\n", $docblockLines)]);
        }
    }
}
