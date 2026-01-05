<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\TokenRunner\DocBlock\MalformWorker;

use ECSPrefix202601\Nette\Utils\Strings;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use Symplify\CodingStandard\TokenRunner\Contract\DocBlock\MalformWorkerInterface;
final class MissingVarNameMalformWorker implements MalformWorkerInterface
{
    /**
     * @var string
     * @see https://regex101.com/r/s1UkZs/1
     */
    private const VAR_WITHOUT_NAME_REGEX = '#^(?<open>\\/\\*\\* @(?:psalm-|phpstan-)?var )(?<type>[\\\\\\w\\|-|]+)(?<close>\\s+\\*\\/)$#';
    /**
     * @param Tokens<Token> $tokens
     */
    public function work(string $docContent, Tokens $tokens, int $position) : string
    {
        if (!Strings::match($docContent, self::VAR_WITHOUT_NAME_REGEX)) {
            return $docContent;
        }
        $nextVariableToken = $this->getNextVariableToken($tokens, $position);
        if (!$nextVariableToken instanceof Token) {
            return $docContent;
        }
        return Strings::replace($docContent, self::VAR_WITHOUT_NAME_REGEX, static function (array $match) use($nextVariableToken) : string {
            return $match['open'] . $match['type'] . ' ' . $nextVariableToken->getContent() . $match['close'];
        });
    }
    /**
     * @param Tokens<Token> $tokens
     */
    private function getNextVariableToken(Tokens $tokens, int $position) : ?Token
    {
        $nextMeaningfulTokenPosition = $tokens->getNextMeaningfulToken($position);
        if ($nextMeaningfulTokenPosition === null) {
            return null;
        }
        $nextToken = $tokens[$nextMeaningfulTokenPosition] ?? null;
        if (!$nextToken instanceof Token) {
            return null;
        }
        if (!$nextToken->isGivenKind(\T_VARIABLE)) {
            return null;
        }
        return $nextToken;
    }
}
