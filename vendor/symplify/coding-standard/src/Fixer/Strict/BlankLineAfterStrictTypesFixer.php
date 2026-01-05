<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\Fixer\Strict;

use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\WhitespacesFixerConfig;
use SplFileInfo;
use Symplify\CodingStandard\Fixer\AbstractSymplifyFixer;
/**
 * Inspired at https://github.com/aidantwoods/PHP-CS-Fixer/tree/feature/DeclareStrictTypesFixer-split
 *
 * @thanks Aidan Woods
 *
 * @see \Symplify\CodingStandard\Tests\Fixer\Strict\BlankLineAfterStrictTypesFixer\BlankLineAfterStrictTypesFixerTest
 */
final class BlankLineAfterStrictTypesFixer extends AbstractSymplifyFixer
{
    /**
     * @readonly
     * @var \PhpCsFixer\WhitespacesFixerConfig
     */
    private $whitespacesFixerConfig;
    /**
     * @var string
     */
    private const ERROR_MESSAGE = 'Strict type declaration has to be followed by empty line';
    /**
     * Generates: "declare(strict_types=1);"
     *
     * @var non-empty-list<Token>
     * @readonly
     */
    private $declareStrictTypeTokens;
    public function __construct(WhitespacesFixerConfig $whitespacesFixerConfig)
    {
        $this->whitespacesFixerConfig = $whitespacesFixerConfig;
        $this->declareStrictTypeTokens = [new Token([\T_DECLARE, 'declare']), new Token('('), new Token([\T_STRING, 'strict_types']), new Token('='), new Token([\T_LNUMBER, '1']), new Token(')'), new Token(';')];
    }
    public function getDefinition() : FixerDefinitionInterface
    {
        return new FixerDefinition(self::ERROR_MESSAGE, []);
    }
    /**
     * @param Tokens<Token> $tokens
     */
    public function isCandidate(Tokens $tokens) : bool
    {
        return $tokens->isAllTokenKindsFound([\T_OPEN_TAG, \T_WHITESPACE, \T_DECLARE, \T_STRING, '=', \T_LNUMBER, ';']);
    }
    /**
     * @param Tokens<Token> $tokens
     */
    public function fix(SplFileInfo $fileInfo, Tokens $tokens) : void
    {
        $sequenceLocation = $tokens->findSequence($this->declareStrictTypeTokens, 1, 15);
        if ($sequenceLocation === null) {
            return;
        }
        \end($sequenceLocation);
        $semicolonPosition = \key($sequenceLocation);
        \reset($sequenceLocation);
        // empty file
        if (!isset($tokens[$semicolonPosition + 2])) {
            return;
        }
        $lineEnding = $this->whitespacesFixerConfig->getLineEnding();
        $tokens->ensureWhitespaceAtIndex($semicolonPosition + 1, 0, $lineEnding . $lineEnding);
    }
}
