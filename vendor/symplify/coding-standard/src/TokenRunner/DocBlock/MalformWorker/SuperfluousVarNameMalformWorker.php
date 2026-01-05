<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\TokenRunner\DocBlock\MalformWorker;

use ECSPrefix202601\Nette\Utils\Strings;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use Symplify\CodingStandard\TokenRunner\Contract\DocBlock\MalformWorkerInterface;
final class SuperfluousVarNameMalformWorker implements MalformWorkerInterface
{
    /**
     * @var string
     * @see https://regex101.com/r/euhrn8/1
     */
    private const THIS_VARIABLE_REGEX = '#\\$this$#';
    /**
     * @var string
     * @see https://regex101.com/r/6XuSGV/1
     */
    private const VAR_VARIABLE_NAME_REGEX = '#(?<tag>@(?:psalm-|phpstan-)?var)(?<type>\\s+[|\\\\\\w]+)?(\\s+)(?<propertyName>\\$[\\w]+)#';
    /**
     * @param Tokens<Token> $tokens
     */
    public function work(string $docContent, Tokens $tokens, int $position) : string
    {
        if ($this->shouldSkip($tokens, $position)) {
            return $docContent;
        }
        $docBlock = new DocBlock($docContent);
        $lines = $docBlock->getLines();
        foreach ($lines as $line) {
            $match = Strings::match($line->getContent(), self::VAR_VARIABLE_NAME_REGEX);
            if ($match === null) {
                continue;
            }
            $newLineContent = Strings::replace($line->getContent(), self::VAR_VARIABLE_NAME_REGEX, static function (array $match) : string {
                $replacement = $match['tag'];
                if ($match['type'] !== []) {
                    $replacement .= $match['type'];
                }
                if (Strings::match($match['propertyName'], self::THIS_VARIABLE_REGEX)) {
                    return $match['tag'] . ' self';
                }
                return $replacement;
            });
            $line->setContent($newLineContent);
        }
        return $docBlock->getContent();
    }
    /**
     * Is property doc block?
     *
     * @param Tokens<Token> $tokens
     */
    private function shouldSkip(Tokens $tokens, int $position) : bool
    {
        $nextMeaningfulTokenPosition = $tokens->getNextMeaningfulToken($position);
        // nothing to change
        if ($nextMeaningfulTokenPosition === null) {
            return \true;
        }
        /** @var Token $nextMeaningfulToken */
        $nextMeaningfulToken = $tokens[$nextMeaningfulTokenPosition];
        // should be protected/private/public/static, to know we're property
        return !$nextMeaningfulToken->isGivenKind([\T_PUBLIC, \T_PROTECTED, \T_PRIVATE, \T_STATIC]);
    }
}
