<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\TokenRunner\DocBlock\MalformWorker;

use ECSPrefix202601\Nette\Utils\Strings;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use Symplify\CodingStandard\TokenRunner\Contract\DocBlock\MalformWorkerInterface;
final class InlineVariableDocBlockMalformWorker implements MalformWorkerInterface
{
    /**
     * @var string
     * @see https://regex101.com/r/GkyV1C/1
     */
    private const SINGLE_ASTERISK_START_REGEX = '#^/\\*\\s+\\*(\\s+@(?:psalm-|phpstan-)?var)#';
    /**
     * @var string
     * @see https://regex101.com/r/9cfhFI/1
     */
    private const SPACE_REGEX = '#\\s+#m';
    /**
     * @var string
     * @see https://regex101.com/r/VpTDCd/1
     */
    private const ASTERISK_LEFTOVERS_REGEX = '#(\\*\\*)(\\s+\\*)#';
    /**
     * @param Tokens<Token> $tokens
     */
    public function work(string $docContent, Tokens $tokens, int $position) : string
    {
        if (!$this->isVariableComment($tokens, $position)) {
            return $docContent;
        }
        // more than 2 newlines - keep it
        if (\substr_count($docContent, "\n") > 2) {
            return $docContent;
        }
        // asterisk start
        $docContent = Strings::replace($docContent, self::SINGLE_ASTERISK_START_REGEX, '/**$1');
        // inline
        $docContent = Strings::replace($docContent, self::SPACE_REGEX, ' ');
        // remove asterisk leftover
        return Strings::replace($docContent, self::ASTERISK_LEFTOVERS_REGEX, '$1');
    }
    /**
     * @param Tokens<Token> $tokens
     */
    private function isVariableComment(Tokens $tokens, int $position) : bool
    {
        $nextPosition = $tokens->getNextMeaningfulToken($position);
        if ($nextPosition === null) {
            return \false;
        }
        $nextNextPosition = $tokens->getNextMeaningfulToken($nextPosition + 2);
        if ($nextNextPosition === null) {
            return \false;
        }
        /** @var Token $nextNextToken */
        $nextNextToken = $tokens[$nextNextPosition];
        if ($nextNextToken->isGivenKind([\T_STATIC, \T_FUNCTION])) {
            return \false;
        }
        // is inline variable
        /** @var Token $nextToken */
        $nextToken = $tokens[$nextPosition];
        return $nextToken->isGivenKind(\T_VARIABLE);
    }
}
