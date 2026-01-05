<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\TokenRunner;

use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use Symplify\CodingStandard\Exception\ShouldNotHappenException;
final class TokenFinder
{
    /**
     * @param Tokens<Token> $tokens
     * @param int|\PhpCsFixer\Tokenizer\Token $position
     */
    public function getPreviousMeaningfulToken(Tokens $tokens, $position) : Token
    {
        if (\is_int($position)) {
            return $this->findPreviousTokenByPosition($tokens, $position);
        }
        return $this->findPreviousTokenByToken($tokens, $position);
    }
    /**
     * @param Tokens<Token> $tokens
     */
    private function findPreviousTokenByPosition(Tokens $tokens, int $position) : Token
    {
        $previousPosition = $position - 1;
        if (!isset($tokens[$previousPosition])) {
            throw new ShouldNotHappenException();
        }
        return $tokens[$previousPosition];
    }
    /**
     * @param Tokens<Token> $tokens
     */
    private function findPreviousTokenByToken(Tokens $tokens, Token $positionToken) : Token
    {
        $position = $this->resolvePositionByToken($tokens, $positionToken);
        return $this->findPreviousTokenByPosition($tokens, $position - 1);
    }
    /**
     * @param Tokens<Token> $tokens
     */
    private function resolvePositionByToken(Tokens $tokens, Token $positionToken) : int
    {
        foreach ($tokens as $position => $token) {
            if ($token === $positionToken) {
                return $position;
            }
        }
        throw new ShouldNotHappenException();
    }
}
