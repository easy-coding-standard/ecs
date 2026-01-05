<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\TokenAnalyzer;

use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
final class DocblockRelatedParamNamesResolver
{
    /**
     * @var list<Token>
     */
    private $functionTokens;
    /**
     * @readonly
     * @var \PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer
     */
    private $functionsAnalyzer;
    public function __construct()
    {
        $this->functionsAnalyzer = new FunctionsAnalyzer();
        $this->functionTokens = [new Token([\T_FUNCTION, 'function'])];
        // only in PHP 7.4+
        if ($this->doesFnTokenExist()) {
            $this->functionTokens[] = new Token([\T_FN, 'fn']);
        }
    }
    /**
     * @return string[]
     * @param Tokens<Token> $tokens
     */
    public function resolve(Tokens $tokens, int $docTokenPosition) : array
    {
        $functionTokenPosition = $tokens->getNextTokenOfKind($docTokenPosition, $this->functionTokens);
        if ($functionTokenPosition === null) {
            return [];
        }
        /** @var array<string, mixed> $functionArgumentAnalyses */
        $functionArgumentAnalyses = $this->functionsAnalyzer->getFunctionArguments($tokens, $functionTokenPosition);
        return \array_keys($functionArgumentAnalyses);
    }
    private function doesFnTokenExist() : bool
    {
        return \PHP_VERSION_ID >= 70400 && \defined('T_FN');
    }
}
