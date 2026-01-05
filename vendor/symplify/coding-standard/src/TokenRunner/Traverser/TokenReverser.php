<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\TokenRunner\Traverser;

use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
final class TokenReverser
{
    /**
     * By convention, tokens should be traversed from the bottom to the top. That way an added node from the bottom,
     * doesn't change index keys of the nodes on the top that are yet to be check.
     *
     * Traversing nodes from the top to the bottom would change the index keys of nodes on the bottom, and would create
     * breaking situations.
     *
     * @param Tokens<Token> $tokens
     * @return Token[]
     */
    public function reverse(Tokens $tokens) : array
    {
        $reversedTokens = \array_reverse($tokens->toArray(), \true);
        // remove null values
        return \array_filter($reversedTokens);
    }
}
