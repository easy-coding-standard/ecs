<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Configuration;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Files\EndFileNewlineSniff as GenericEndFileNewlineSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Files\EndFileNoNewlineSniff;
use PHP_CodeSniffer\Standards\PSR2\Sniffs\Files\EndFileNewlineSniff as Psr2EndFileNewlineSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\Strings\DoubleQuoteUsageSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\SuperfluousWhitespaceSniff;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\Fixer\Whitespace\NoTrailingWhitespaceFixer;
use PhpCsFixer\Fixer\Whitespace\SingleBlankLineAtEofFixer;
use Symfony\Component\Finder\Finder;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\Config\Level\SpacesLevel;
use Symplify\EasyCodingStandard\Configuration\EditorConfig\EditorConfigFactory;
use Symplify\EasyCodingStandard\Configuration\EditorConfig\EndOfLine;
use Symplify\EasyCodingStandard\Configuration\EditorConfig\IndentStyle;
use Symplify\EasyCodingStandard\Configuration\EditorConfig\QuoteType;
use Symplify\EasyCodingStandard\Configuration\Levels\LevelRulesResolver;
use Symplify\EasyCodingStandard\Exception\Configuration\InitializationException;
use Symplify\EasyCodingStandard\Exception\Configuration\SuperfluousConfigurationException;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

/**
 * @api
 */
final class ECSConfigBuilder
{
    /**
     * @var string[]
     */
    private array $paths = [];

    /**
     * @var string[]
     */
    private array $sets = [];

    /**
     * @var string[]
     */
    private array $dynamicSets = [];

    /**
     * @var array<mixed>
     */
    private array $skip = [];

    /**
     * @var array<class-string<Sniff|FixerInterface>>
     */
    private array $rules = [];

    /**
     * @var array<class-string<(FixerInterface | Sniff)>, mixed>
     */
    private array $rulesWithConfiguration = [];

    /**
     * @var string[]
     */
    private array $fileExtensions = [];

    private ?string $cacheDirectory = null;

    private ?string $cacheNamespace = null;

    /**
     * @var Option::INDENTATION_*
     */
    private ?string $indentation = null;

    private ?string $lineEnding = null;

    private ?bool $parallel = null;

    private int $parallelTimeoutSeconds = 120;

    private int $parallelMaxNumberOfProcess = 32;

    private int $parallelJobSize = 20;

    private ?bool $reportingRealPath = null;

    private ?bool $useEditorConfig = null;

    public function __invoke(ECSConfig $ecsConfig): void
    {
        $this->applyEditorConfigSettings();

        if ($this->sets !== []) {
            $ecsConfig->sets($this->sets);
        }

        if ($this->dynamicSets !== []) {
            $ecsConfig->dynamicSets($this->dynamicSets);
        }

        if ($this->paths !== []) {
            $ecsConfig->paths($this->paths);
        }

        if ($this->skip !== []) {
            $ecsConfig->skip($this->skip);
        }

        if ($this->rules !== []) {
            $ecsConfig->rules($this->rules);
        }

        if ($this->rulesWithConfiguration !== []) {
            $ecsConfig->rulesWithConfiguration($this->rulesWithConfiguration);
        }

        if ($this->fileExtensions !== []) {
            $ecsConfig->fileExtensions($this->fileExtensions);
        }

        if ($this->cacheDirectory !== null) {
            $ecsConfig->cacheDirectory($this->cacheDirectory);
        }

        if ($this->cacheNamespace !== null) {
            $ecsConfig->cacheNamespace($this->cacheNamespace);
        }

        if ($this->indentation !== null) {
            $ecsConfig->indentation($this->indentation);
        }

        if ($this->lineEnding !== null) {
            $ecsConfig->lineEnding($this->lineEnding);
        }

        if ($this->parallel !== null) {
            if ($this->parallel) {
                $ecsConfig->parallel(
                    seconds: $this->parallelTimeoutSeconds,
                    maxNumberOfProcess: $this->parallelMaxNumberOfProcess,
                    jobSize: $this->parallelJobSize
                );
            } else {
                $ecsConfig->disableParallel();
            }
        }

        if ($this->reportingRealPath !== null) {
            $ecsConfig->reportingRealPath($this->reportingRealPath);
        }
    }

    /**
     * @param string[] $paths
     */
    public function withPaths(array $paths): self
    {
        $this->paths = $paths;

        return $this;
    }

    /**
     * @param array<mixed> $skip
     */
    public function withSkip(array $skip): self
    {
        $this->skip = $skip;

        return $this;
    }

    /**
     * Include PHP files from the root directory,
     * typically ecs.php, rector.php etc.
     */
    public function withRootFiles(): self
    {
        $rootPhpFilesFinder = (new Finder())->files()
            ->in(getcwd())
            ->depth(0)
            ->name('*.php');

        foreach ($rootPhpFilesFinder as $rootPhpFileFinder) {
            $this->paths[] = $rootPhpFileFinder->getRealPath();
        }

        return $this;
    }

    public function withPreparedSets(
        /** @see SetList::PSR_12 */
        bool $psr12 = false,
        /** @see SetList::COMMON */
        bool $common = false,
        /** @see SetList::SYMPLIFY */
        bool $symplify = false,
        /** @see SetList::LARAVEL */
        bool $laravel = false,

        // common sets
        /** @see SetList::ARRAY */
        bool $arrays = false,
        /** @see SetList::COMMENTS */
        bool $comments = false,
        /** @see SetList::DOCBLOCK */
        bool $docblocks = false,
        /** @see SetList::SPACES */
        bool $spaces = false,
        /** @see SetList::NAMESPACES */
        bool $namespaces = false,
        /** @see SetList::CONTROL_STRUCTURES */
        bool $controlStructures = false,
        /** @see SetList::PHPUNIT */
        bool $phpunit = false,
        /** @see SetList::STRICT */
        bool $strict = false,
        /** @see SetList::CLEAN_CODE */
        bool $cleanCode = false,
    ): self {
        if (func_get_args() === []) {
            throw new InitializationException(
                'Pick at least one set in "->withPreparedSets()" in your ecs.php using named arguments, e.g. "->withPreparedSets(spaces: true)"'
            );
        }

        if ($psr12) {
            $this->sets[] = SetList::PSR_12;
        }

        if ($common) {
            // include all "common" sets
            $this->sets[] = SetList::COMMON;

            if (($alreadyIncludedSets = array_keys(array_filter([
                'arrays' => $arrays,
                'spaces' => $spaces,
                'namespaces' => $namespaces,
                'docblocks' => $docblocks,
                'controlStructures' => $controlStructures,
                'phpunit' => $phpunit,
                'comments' => $comments,
            ]))) !== []) {
                throw new SuperfluousConfigurationException(
                    sprintf(
                        'The following sets are already included in the "common" set: %s. Please remove them.',
                        implode(', ', $alreadyIncludedSets)
                    )
                );
            }
        } else {
            if ($arrays) {
                $this->sets[] = SetList::ARRAY;
            }

            if ($spaces) {
                $this->sets[] = SetList::SPACES;
            }

            if ($namespaces) {
                $this->sets[] = SetList::NAMESPACES;
            }

            if ($docblocks) {
                $this->sets[] = SetList::DOCBLOCK;
            }

            if ($controlStructures) {
                $this->sets[] = SetList::CONTROL_STRUCTURES;
            }

            if ($phpunit) {
                $this->sets[] = SetList::PHPUNIT;
            }

            if ($comments) {
                $this->sets[] = SetList::COMMENTS;
            }
        }

        if ($strict) {
            $this->sets[] = SetList::STRICT;
        }

        if ($cleanCode) {
            $this->sets[] = SetList::CLEAN_CODE;
        }

        if ($symplify) {
            $this->sets[] = SetList::SYMPLIFY;
        }

        if ($laravel) {
            $this->sets[] = SetList::LARAVEL;
        }

        return $this;
    }

    public function withPhpCsFixerSets(
        bool $doctrineAnnotation = false,
        bool $per = false,
        bool $perCS = false,
        bool $perCS10 = false,
        bool $perCS10Risky = false,
        bool $perCS20 = false,
        bool $perCS20Risky = false,
        bool $perCSRisky = false,
        bool $perRisky = false,
        bool $php54Migration = false,
        bool $php56MigrationRisky = false,
        bool $php70Migration = false,
        bool $php70MigrationRisky = false,
        bool $php71Migration = false,
        bool $php71MigrationRisky = false,
        bool $php73Migration = false,
        bool $php74Migration = false,
        bool $php74MigrationRisky = false,
        bool $php80Migration = false,
        bool $php80MigrationRisky = false,
        bool $php81Migration = false,
        bool $php82Migration = false,
        bool $php83Migration = false,
        bool $php84Migration = false,
        bool $phpunit30MigrationRisky = false,
        bool $phpunit32MigrationRisky = false,
        bool $phpunit35MigrationRisky = false,
        bool $phpunit43MigrationRisky = false,
        bool $phpunit48MigrationRisky = false,
        bool $phpunit50MigrationRisky = false,
        bool $phpunit52MigrationRisky = false,
        bool $phpunit54MigrationRisky = false,
        bool $phpunit55MigrationRisky = false,
        bool $phpunit56MigrationRisky = false,
        bool $phpunit57MigrationRisky = false,
        bool $phpunit60MigrationRisky = false,
        bool $phpunit75MigrationRisky = false,
        bool $phpunit84MigrationRisky = false,
        bool $phpunit100MigrationRisky = false,
        bool $psr1 = false,
        bool $psr2 = false,
        bool $psr12 = false,
        bool $psr12Risky = false,
        bool $phpCsFixer = false,
        bool $phpCsFixerRisky = false,
        bool $symfony = false,
        bool $symfonyRisky = false,
        bool $perCS30 = false,
        bool $perCS30Risky = false,
        bool $php81MigrationRisky = false,
        bool $php82MigrationRisky = false,
        bool $php83MigrationRisky = false,
        bool $php84MigrationRisky = false,
        bool $php85Migration = false,
        bool $php85MigrationRisky = false,
        bool $auto = false,
        bool $autoRisky = false,
        bool $autoPHPMigration = false,
        bool $autoPHPMigrationRisky = false,
        bool $autoPHPUnitMigrationRisky = false,
    ): self {
        if ($doctrineAnnotation) {
            $this->dynamicSets[] = '@DoctrineAnnotation';
        }

        if ($per) {
            $this->dynamicSets[] = '@PER';
        }

        if ($perCS) {
            $this->dynamicSets[] = '@PER-CS';
        }

        if ($perCS10) {
            $this->dynamicSets[] = '@PER-CS1x0';
        }

        if ($perCS10Risky) {
            $this->dynamicSets[] = '@PER-CS1x0:risky';
        }

        if ($perCS20) {
            $this->dynamicSets[] = '@PER-CS2x0';
        }

        if ($perCS20Risky) {
            $this->dynamicSets[] = '@PER-CS2x0:risky';
        }

        if ($perCS30) {
            $this->dynamicSets[] = '@PER-CS3x0';
        }

        if ($perCS30Risky) {
            $this->dynamicSets[] = '@PER-CS3x0:risky';
        }

        if ($perCSRisky) {
            $this->dynamicSets[] = '@PER-CS:risky';
        }

        if ($perRisky) {
            $this->dynamicSets[] = '@PER:risky';
        }

        if ($php54Migration) {
            $this->dynamicSets[] = '@PHP5x4Migration';
        }

        if ($php56MigrationRisky) {
            $this->dynamicSets[] = '@PHP5x6Migration:risky';
        }

        if ($php70Migration) {
            $this->dynamicSets[] = '@PHP7x0Migration';
        }

        if ($php70MigrationRisky) {
            $this->dynamicSets[] = '@PHP7x0Migration:risky';
        }

        if ($php71Migration) {
            $this->dynamicSets[] = '@PHP7x1Migration';
        }

        if ($php71MigrationRisky) {
            $this->dynamicSets[] = '@PHP7x1Migration:risky';
        }

        if ($php73Migration) {
            $this->dynamicSets[] = '@PHP7x3Migration';
        }

        if ($php74Migration) {
            $this->dynamicSets[] = '@PHP7x4Migration';
        }

        if ($php74MigrationRisky) {
            $this->dynamicSets[] = '@PHP7x4Migration:risky';
        }

        if ($php80Migration) {
            $this->dynamicSets[] = '@PHP8x0Migration';
        }

        if ($php80MigrationRisky) {
            $this->dynamicSets[] = '@PHP8x0Migration:risky';
        }

        if ($php81Migration) {
            $this->dynamicSets[] = '@PHP8x1Migration';
        }

        if ($php81MigrationRisky) {
            $this->dynamicSets[] = '@PHP8x1Migration:risky';
        }

        if ($php82Migration) {
            $this->dynamicSets[] = '@PHP8x2Migration';
        }

        if ($php82MigrationRisky) {
            $this->dynamicSets[] = '@PHP8x2Migration:risky';
        }

        if ($php83Migration) {
            $this->dynamicSets[] = '@PHP8x3Migration';
        }

        if ($php83MigrationRisky) {
            $this->dynamicSets[] = '@PHP8x3Migration:risky';
        }

        if ($php84Migration) {
            $this->dynamicSets[] = '@PHP8x4Migration';
        }

        if ($php84MigrationRisky) {
            $this->dynamicSets[] = '@PHP8x4Migration:risky';
        }

        if ($php85Migration) {
            $this->dynamicSets[] = '@PHP8x5Migration';
        }

        if ($php85MigrationRisky) {
            $this->dynamicSets[] = '@PHP8x5Migration:risky';
        }

        if ($phpunit30MigrationRisky) {
            $this->dynamicSets[] = '@PHPUnit3x0Migration:risky';
        }

        if ($phpunit32MigrationRisky) {
            $this->dynamicSets[] = '@PHPUnit3x2Migration:risky';
        }

        if ($phpunit35MigrationRisky) {
            $this->dynamicSets[] = '@PHPUnit3x5Migration:risky';
        }

        if ($phpunit43MigrationRisky) {
            $this->dynamicSets[] = '@PHPUnit4x3Migration:risky';
        }

        if ($phpunit48MigrationRisky) {
            $this->dynamicSets[] = '@PHPUnit4x8Migration:risky';
        }

        if ($phpunit50MigrationRisky) {
            $this->dynamicSets[] = '@PHPUnit5x0Migration:risky';
        }

        if ($phpunit52MigrationRisky) {
            $this->dynamicSets[] = '@PHPUnit5x2Migration:risky';
        }

        if ($phpunit54MigrationRisky) {
            $this->dynamicSets[] = '@PHPUnit5x4Migration:risky';
        }

        if ($phpunit55MigrationRisky) {
            $this->dynamicSets[] = '@PHPUnit5x5Migration:risky';
        }

        if ($phpunit56MigrationRisky) {
            $this->dynamicSets[] = '@PHPUnit5x6Migration:risky';
        }

        if ($phpunit57MigrationRisky) {
            $this->dynamicSets[] = '@PHPUnit5x7Migration:risky';
        }

        if ($phpunit60MigrationRisky) {
            $this->dynamicSets[] = '@PHPUnit6x0Migration:risky';
        }

        if ($phpunit75MigrationRisky) {
            $this->dynamicSets[] = '@PHPUnit7x5Migration:risky';
        }

        if ($phpunit84MigrationRisky) {
            $this->dynamicSets[] = '@PHPUnit8x4Migration:risky';
        }

        if ($phpunit100MigrationRisky) {
            $this->dynamicSets[] = '@PHPUnit10x0Migration:risky';
        }

        if ($psr1) {
            $this->dynamicSets[] = '@PSR1';
        }

        if ($psr2) {
            $this->dynamicSets[] = '@PSR2';
        }

        if ($psr12) {
            $this->dynamicSets[] = '@PSR12';
        }

        if ($psr12Risky) {
            $this->dynamicSets[] = '@PSR12:risky';
        }

        if ($phpCsFixer) {
            $this->dynamicSets[] = '@PhpCsFixer';
        }

        if ($phpCsFixerRisky) {
            $this->dynamicSets[] = '@PhpCsFixer:risky';
        }

        if ($symfony) {
            $this->dynamicSets[] = '@Symfony';
        }

        if ($symfonyRisky) {
            $this->dynamicSets[] = '@Symfony:risky';
        }

        if ($auto) {
            $this->dynamicSets[] = '@auto';
        }

        if ($autoRisky) {
            $this->dynamicSets[] = '@auto:risky';
        }

        if ($autoPHPMigration) {
            $this->dynamicSets[] = '@autoPHPMigration';
        }

        if ($autoPHPMigrationRisky) {
            $this->dynamicSets[] = '@autoPHPMigration:risky';
        }

        if ($autoPHPUnitMigrationRisky) {
            $this->dynamicSets[] = '@autoPHPUnitMigration:risky';
        }

        return $this;
    }

    /**
     * @param string[] $sets
     */
    public function withSets(array $sets): self
    {
        $this->sets = [...$this->sets, ...$sets];

        return $this;
    }

    /**
     * @param array<class-string<Sniff|FixerInterface>> $rules
     */
    public function withRules(array $rules): self
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @param string[] $fileExtensions
     */
    public function withFileExtensions(array $fileExtensions): self
    {
        $this->fileExtensions = $fileExtensions;

        return $this;
    }

    public function withCache(?string $directory = null, ?string $namespace = null): self
    {
        $this->cacheDirectory = $directory;
        $this->cacheNamespace = $namespace;

        return $this;
    }

    public function withEditorConfig(bool $enabled = true): self
    {
        $this->useEditorConfig = $enabled;

        return $this;
    }

    /**
     * @param Option::INDENTATION_*|null $indentation
     */
    public function withSpacing(?string $indentation = null, ?string $lineEnding = null): self
    {
        $this->indentation = $indentation;
        $this->lineEnding = $lineEnding;

        return $this;
    }

    /**
     * @param class-string<(FixerInterface | Sniff)> $checkerClass
     * @param mixed[] $configuration
     */
    public function withConfiguredRule(string $checkerClass, array $configuration): self
    {
        $this->rulesWithConfiguration[$checkerClass] = $configuration;

        return $this;
    }

    public function withParallel(
        ?int $timeoutSeconds = null,
        ?int $maxNumberOfProcess = null,
        ?int $jobSize = null
    ): self {
        $this->parallel = true;

        if (is_int($timeoutSeconds)) {
            $this->parallelTimeoutSeconds = $timeoutSeconds;
        }

        if (is_int($maxNumberOfProcess)) {
            $this->parallelMaxNumberOfProcess = $maxNumberOfProcess;
        }

        if (is_int($jobSize)) {
            $this->parallelJobSize = $jobSize;
        }

        return $this;
    }

    public function withoutParallel(): self
    {
        $this->parallel = false;

        return $this;
    }

    public function withRealPathReporting(bool $absolutePath = true): self
    {
        $this->reportingRealPath = $absolutePath;

        return $this;
    }

    /**
     * Raise your spacing coverage from the safest rules
     * to more affecting ones, one level at a time.
     */
    public function withSpacesLevel(int $level): self
    {
        $levelRules = LevelRulesResolver::resolve($level, SpacesLevel::RULES, __METHOD__);

        foreach ($levelRules as $levelRule) {
            if (isset(SpacesLevel::RULE_CONFIGURATIONS[$levelRule])) {
                $this->rulesWithConfiguration[$levelRule] = SpacesLevel::RULE_CONFIGURATIONS[$levelRule];
                continue;
            }

            $this->rules[] = $levelRule;
        }

        return $this;
    }

    private function applyEditorConfigSettings(): void
    {
        if (! $this->useEditorConfig) {
            return;
        }

        /**
         * PHP CS Fixer handles most of this, code sniffer just needs to stay
         * out of out way. Luckily, we have a pass to make sure it does!
         *
         * This does introduce a quirk that if someone manually disables a Fixer
         * rule, but does not enable the equivalent Sniffer rule, that
         * EditorConfig setting won't be respected. But why would they do that?
         *
         * @see Symplify\EasyCodingStandard\DependencyInjection\CompilerPass\RemoveMutualCheckersCompilerPass
         */
        $editorConfig = (new EditorConfigFactory())->load();

        if ($editorConfig->indentStyle !== null) {
            $this->indentation = match ($editorConfig->indentStyle) {
                IndentStyle::Space => Option::INDENTATION_SPACES,
                IndentStyle::Tab => Option::INDENTATION_TAB,
                default => Option::INDENTATION_SPACES,
            };
        }

        if ($editorConfig->endOfLine !== null) {
            $this->lineEnding = match ($editorConfig->endOfLine) {
                EndOfLine::Posix => "\n",
                EndOfLine::Legacy => "\r",
                EndOfLine::Windows => "\r\n",
                default => "\n",
            };
        }

        if ($editorConfig->maxLineLength) {
            $this->rulesWithConfiguration[LineLengthFixer::class] = [
                ...($this->rulesWithConfiguration[LineLengthFixer::class] ?? []),
                'line_length' => $editorConfig->maxLineLength,
            ];
        }

        if ($editorConfig->trimTrailingWhitespace === true) {
            $this->rules[] = NoTrailingWhitespaceFixer::class;
        } elseif ($editorConfig->trimTrailingWhitespace === false) {
            $this->skip = [...$this->skip, NoTrailingWhitespaceFixer::class, SuperfluousWhitespaceSniff::class];
        }

        if ($editorConfig->insertFinalNewline === true) {
            $this->rules[] = SingleBlankLineAtEofFixer::class;
        } elseif ($editorConfig->insertFinalNewline === false) {
            $this->rules[] = EndFileNoNewlineSniff::class;
            $this->skip[] = [
                SingleBlankLineAtEofFixer::class,
                Psr2EndFileNewlineSniff::class,
                GenericEndFileNewlineSniff::class,
            ];
        }

        if ($editorConfig->quoteType === QuoteType::Auto) {
            $this->rules[] = SingleQuoteFixer::class;
        } elseif ($editorConfig->quoteType === QuoteType::Single) {
            $this->rulesWithConfiguration[SingleQuoteFixer::class] = [
                'strings_containing_single_quote_chars' => true,
            ];
        } elseif ($editorConfig->quoteType === QuoteType::Double) {
            $this->skip = [...$this->skip, SingleQuoteFixer::class, DoubleQuoteUsageSniff::class];
        }
    }
}
