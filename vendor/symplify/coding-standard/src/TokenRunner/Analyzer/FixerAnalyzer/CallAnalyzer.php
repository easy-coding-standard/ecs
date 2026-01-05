<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\TokenRunner\Analyzer\FixerAnalyzer;

use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
final class CallAnalyzer
{
    /**
     * @param Tokens<Token> $tokens
     */
    public function isMethodCall(Tokens $tokens, int $bracketPosition) : bool
    {
        $objectToken = new Token([\T_OBJECT_OPERATOR, '->']);
        $whitespaceToken = new Token([\T_WHITESPACE, ' ']);
        $previousTokenOfKindPosition = $tokens->getPrevTokenOfKind($bracketPosition, [$objectToken, $whitespaceToken]);
        // probably a function call
        if ($previousTokenOfKindPosition === null) {
            return \false;
        }
        /** @var Token $token */
        $token = $tokens[$previousTokenOfKindPosition];
        return $token->isGivenKind(\T_OBJECT_OPERATOR);
    }
}
