<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\TokenRunner\Contract\DocBlock;

use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
interface MalformWorkerInterface
{
    /**
     * @param Tokens<Token> $tokens
     */
    public function work(string $docContent, Tokens $tokens, int $position) : string;
}
