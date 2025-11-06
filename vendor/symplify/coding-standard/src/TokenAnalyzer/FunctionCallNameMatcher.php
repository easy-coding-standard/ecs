<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\TokenAnalyzer;

use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use Throwable;
final class FunctionCallNameMatcher
{
    /**
     * We go through tokens from down to up, so we need to find ")" and then the start of function
     *
     * @param Tokens<Token> $tokens
     */
    public function matchName(Tokens $tokens, int $position) : ?int
    {
        try {
            $blockStart = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $position);
        } catch (Throwable $exception) {
            // not a block start
            return null;
        }
        $previousTokenPosition = $blockStart - 1;
        /** @var Token $possibleMethodNameToken */
        $possibleMethodNameToken = $tokens[$previousTokenPosition];
        // not a "methodCall()"
        if (!$possibleMethodNameToken->isGivenKind(\T_STRING)) {
            return null;
        }
        // starts with small letter?
        $content = $possibleMethodNameToken->getContent();
        if (!\ctype_lower($content[0])) {
            return null;
        }
        // is "someCall()"? we don't care, there are no arguments
        if ($tokens[$blockStart + 1]->equals(')')) {
            return null;
        }
        return $previousTokenPosition;
    }
}
