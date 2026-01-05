<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\FixerRunner;

use PhpCsFixer\WhitespacesFixerConfig;
use Symplify\EasyCodingStandard\DependencyInjection\SimpleParameterProvider;
use Symplify\EasyCodingStandard\Exception\Configuration\WhitespaceConfigurationException;
use Symplify\EasyCodingStandard\FixerRunner\ValueObject\Spacing;
use Symplify\EasyCodingStandard\ValueObject\Option;

/**
 * @api
 */
final class WhitespacesFixerConfigFactory
{
    /**
     * @var string[]
     */
    private const array ALLOWED_VALUES = ['tab', 'spaces', Spacing::TWO_SPACES, Spacing::FOUR_SPACES, Spacing::ONE_TAB];

    /**
     * @api
     */
    public function create(): WhitespacesFixerConfig
    {
        $lineEnding = SimpleParameterProvider::getStringParameter(Option::LINE_ENDING);

        if ($lineEnding === '\n') {
            $lineEnding = "\n";
        }

        $indentation = $this->resolveIndentation();

        assert($lineEnding !== '');
        assert($indentation !== '');

        return new WhitespacesFixerConfig($indentation, $lineEnding);
    }

    private function resolveIndentation(): string
    {
        $indentation = SimpleParameterProvider::getStringParameter(Option::INDENTATION);
        if ($this->isOneTab($indentation)) {
            return Spacing::ONE_TAB;
        }

        if ($indentation === Spacing::TWO_SPACES) {
            return Spacing::TWO_SPACES;
        }

        if ($this->isFourSpaces($indentation)) {
            return Spacing::FOUR_SPACES;
        }

        throw new WhitespaceConfigurationException(sprintf(
            'Value "%s" is not supported in "$ecsConfig->indentation(...)".%sUse one of: "%s".',
            $indentation,
            PHP_EOL,
            implode('", "', self::ALLOWED_VALUES)
        ));
    }

    private function isOneTab(string $indentation): bool
    {
        if ($indentation === 'tab') {
            return true;
        }

        return $indentation === Spacing::ONE_TAB;
    }

    private function isFourSpaces(string $indentation): bool
    {
        if ($indentation === 'spaces') {
            return true;
        }

        return $indentation === Spacing::FOUR_SPACES;
    }
}
