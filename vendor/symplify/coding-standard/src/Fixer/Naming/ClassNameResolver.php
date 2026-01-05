<?php

namespace Symplify\CodingStandard\Fixer\Naming;

use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;
final class ClassNameResolver
{
    /**
     * @var array<string, string>
     */
    private $classNameByFilePath = [];
    /**
     * @param Tokens<Token> $tokens
     */
    public function resolveClassName(SplFileInfo $splFileInfo, Tokens $tokens) : ?string
    {
        $filePath = $splFileInfo->getRealPath();
        if (isset($this->classNameByFilePath[$filePath])) {
            return $this->classNameByFilePath[$filePath];
        }
        $classLikeName = $this->resolveFromTokens($tokens);
        if (!\is_string($classLikeName)) {
            return null;
        }
        $this->classNameByFilePath[$filePath] = $classLikeName;
        return $classLikeName;
    }
    /**
     * @param Tokens<Token> $tokens
     */
    private function resolveFromTokens(Tokens $tokens) : ?string
    {
        foreach ($tokens as $position => $token) {
            if (!$token->isGivenKind([\T_CLASS, \T_TRAIT, \T_INTERFACE])) {
                continue;
            }
            $nextNextMeaningfulTokenIndex = $tokens->getNextMeaningfulToken($position + 1);
            if ($nextNextMeaningfulTokenIndex === null) {
                continue;
            }
            $nextNextMeaningfulToken = $tokens[$nextNextMeaningfulTokenIndex];
            // skip anonymous classes
            if (!$nextNextMeaningfulToken->isGivenKind(\T_STRING)) {
                continue;
            }
            return $nextNextMeaningfulToken->getContent();
        }
        return null;
    }
}
