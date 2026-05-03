<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\Fixer\Commenting;

use ECSPrefix202605\Nette\Utils\Strings;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;
use Symplify\CodingStandard\Fixer\AbstractSymplifyFixer;
use Symplify\CodingStandard\TokenRunner\Contract\DocBlock\MalformWorkerInterface;
use Symplify\CodingStandard\TokenRunner\DocBlock\MalformWorker\InlineVariableDocBlockMalformWorker;
use Symplify\CodingStandard\TokenRunner\DocBlock\MalformWorker\InlineVarMalformWorker;
use Symplify\CodingStandard\TokenRunner\DocBlock\MalformWorker\MissingParamNameMalformWorker;
use Symplify\CodingStandard\TokenRunner\DocBlock\MalformWorker\MissingVarNameMalformWorker;
use Symplify\CodingStandard\TokenRunner\DocBlock\MalformWorker\ParamNameReferenceMalformWorker;
use Symplify\CodingStandard\TokenRunner\DocBlock\MalformWorker\ParamNameTypoMalformWorker;
use Symplify\CodingStandard\TokenRunner\DocBlock\MalformWorker\SuperfluousReturnNameMalformWorker;
use Symplify\CodingStandard\TokenRunner\DocBlock\MalformWorker\SuperfluousVarNameMalformWorker;
use Symplify\CodingStandard\TokenRunner\DocBlock\MalformWorker\SwitchedTypeAndNameMalformWorker;
use Symplify\CodingStandard\TokenRunner\Traverser\TokenReverser;
/**
 * @see \Symplify\CodingStandard\Tests\Fixer\Commenting\ParamReturnAndVarTagMalformsFixer\ParamReturnAndVarTagMalformsFixerTest
 */
final class ParamReturnAndVarTagMalformsFixer extends AbstractSymplifyFixer
{
    /**
     * @readonly
     * @var \Symplify\CodingStandard\TokenRunner\Traverser\TokenReverser
     */
    private $tokenReverser;
    /**
     * @var string
     */
    private const ERROR_MESSAGE = 'Fixes @param, @return, @var and inline @var annotations broken formats';
    /**
     * @var string
     * @see https://regex101.com/r/Nlxkd9/1
     */
    private const TYPE_ANNOTATION_REGEX = '#@(psalm-|phpstan-)?(param|return|var)#';
    /**
     * @var MalformWorkerInterface[]
     */
    private $malformWorkers = [];
    public function __construct(InlineVariableDocBlockMalformWorker $inlineVariableDocBlockMalformWorker, InlineVarMalformWorker $inlineVarMalformWorker, MissingParamNameMalformWorker $missingParamNameMalformWorker, MissingVarNameMalformWorker $missingVarNameMalformWorker, ParamNameReferenceMalformWorker $paramNameReferenceMalformWorker, ParamNameTypoMalformWorker $paramNameTypoMalformWorker, SuperfluousReturnNameMalformWorker $superfluousReturnNameMalformWorker, SuperfluousVarNameMalformWorker $superfluousVarNameMalformWorker, SwitchedTypeAndNameMalformWorker $switchedTypeAndNameMalformWorker, TokenReverser $tokenReverser)
    {
        $this->tokenReverser = $tokenReverser;
        $this->malformWorkers = [$inlineVariableDocBlockMalformWorker, $inlineVarMalformWorker, $missingParamNameMalformWorker, $missingVarNameMalformWorker, $paramNameReferenceMalformWorker, $paramNameTypoMalformWorker, $superfluousReturnNameMalformWorker, $superfluousVarNameMalformWorker, $switchedTypeAndNameMalformWorker];
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
        if (!$tokens->isAnyTokenKindsFound([\T_DOC_COMMENT, \T_COMMENT])) {
            return \false;
        }
        $reversedTokens = $this->tokenReverser->reverse($tokens);
        foreach ($reversedTokens as $index => $token) {
            if (!$token->isGivenKind([\T_CALLABLE])) {
                continue;
            }
            if (!(isset($tokens[$index + 3]) && $tokens[$index + 3]->getContent() === ')')) {
                continue;
            }
            return \false;
        }
        return $tokens->isAnyTokenKindsFound([\T_FUNCTION, \T_VARIABLE]);
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
            $docContent = $token->getContent();
            if (!Strings::match($docContent, self::TYPE_ANNOTATION_REGEX)) {
                continue;
            }
            $originalDocContent = $docContent;
            foreach ($this->malformWorkers as $malformWorker) {
                $docContent = $malformWorker->work($docContent, $tokens, $index);
            }
            if ($docContent === $originalDocContent) {
                continue;
            }
            $tokens[$index] = new Token([\T_DOC_COMMENT, $docContent]);
        }
    }
    /**
     * Must run before
     *
     * @see \PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer::getPriority()
     */
    public function getPriority() : int
    {
        return -37;
    }
}
