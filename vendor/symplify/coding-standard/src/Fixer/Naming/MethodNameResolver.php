<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\Fixer\Naming;

use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
final class MethodNameResolver
{
    /**
     * @param Tokens<Token> $tokens
     */
    public function resolve(Tokens $tokens, int $currentPosition): ?string
    {
        foreach ($tokens as $position => $token) {
            if ($position <= $currentPosition) {
                continue;
            }
            if (!$token->isGivenKind([\T_FUNCTION])) {
                continue;
            }
            $nextNextMeaningfulTokenIndex = $tokens->getNextMeaningfulToken($position + 1);
            if ($nextNextMeaningfulTokenIndex === null) {
                continue;
            }
            $nextNextMeaningfulToken = $tokens[$nextNextMeaningfulTokenIndex];
            // skip anonymous functions
            if (!$nextNextMeaningfulToken->isGivenKind(\T_STRING)) {
                continue;
            }
            return $nextNextMeaningfulToken->getContent();
        }
        return null;
    }
}
