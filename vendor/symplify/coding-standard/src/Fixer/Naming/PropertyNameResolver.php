<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\Fixer\Naming;

use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
final class PropertyNameResolver
{
    /**
     * @param Tokens<Token> $tokens
     */
    public function resolve(Tokens $tokens, int $currentPosition) : ?string
    {
        foreach ($tokens as $position => $token) {
            if ($position <= $currentPosition) {
                continue;
            }
            if (!$token->isGivenKind([\T_VARIABLE])) {
                continue;
            }
            return $token->getContent();
        }
        return null;
    }
}
