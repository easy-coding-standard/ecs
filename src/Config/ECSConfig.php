<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Config;

use Illuminate\Container\Container;
use Override;
use PHP_CodeSniffer\Sniffs\Sniff;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerFactory;
use PhpCsFixer\RuleSet\RuleSet;
use PhpCsFixer\WhitespacesFixerConfig;
use Symplify\EasyCodingStandard\Configuration\ECSConfigBuilder;
use Symplify\EasyCodingStandard\Contract\Console\Output\OutputFormatterInterface;
use Symplify\EasyCodingStandard\DependencyInjection\CompilerPass\ConflictingCheckersCompilerPass;
use Symplify\EasyCodingStandard\DependencyInjection\CompilerPass\RemoveExcludedCheckersCompilerPass;
use Symplify\EasyCodingStandard\DependencyInjection\CompilerPass\RemoveMutualCheckersCompilerPass;
use Symplify\EasyCodingStandard\DependencyInjection\SimpleParameterProvider;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @api
 */
final class ECSConfig extends Container
{
    /**
     * @var string[]
     */
    private const array AUTOTAG_INTERFACES = [Sniff::class, FixerInterface::class, OutputFormatterInterface::class];

    public static function configure(): ECSConfigBuilder
    {
        return new ECSConfigBuilder();
    }

    /**
     * @param string[] $paths
     */
    public function paths(array $paths): void
    {
        Assert::allString($paths);

        SimpleParameterProvider::setParameter(Option::PATHS, $paths);
    }

    /**
     * @param list<string>|array<class-string<Sniff|FixerInterface>, list<string>|null> $skips
     */
    public function skip(array $skips): void
    {
        SimpleParameterProvider::addParameter(Option::SKIP, $skips);
    }

    /**
     * @param string[] $sets
     */
    public function sets(array $sets): void
    {
        Assert::allString($sets);
        Assert::allFileExists($sets);

        foreach ($sets as $set) {
            $this->import($set);
        }
    }

    /**
     * @param class-string<Sniff|FixerInterface> $checkerClass
     */
    public function rule(string $checkerClass): void
    {
        $this->assertCheckerClass($checkerClass);

        $this->singleton($checkerClass);
        $this->autowireWhitespaceAwareFixer($checkerClass);

        if (is_a($checkerClass, ConfigurableFixerInterface::class, true)) {
            $this->extend(
                $checkerClass,
                static function (ConfigurableFixerInterface $configurableFixer): ConfigurableFixerInterface {
                    $configurableFixer->configure([]);
                    return $configurableFixer;
                }
            );
        }
    }

    /**
     * @param array<class-string<Sniff|FixerInterface>> $checkerClasses
     */
    public function rules(array $checkerClasses): void
    {
        $this->ensureCheckerClassesAreUnique($checkerClasses);

        foreach ($checkerClasses as $checkerClass) {
            $this->rule($checkerClass);
        }
    }

    /**
     * @param class-string<Sniff|FixerInterface> $checkerClass
     * @param mixed[] $configuration
     */
    public function ruleWithConfiguration(string $checkerClass, array $configuration): void
    {
        $this->assertCheckerClass($checkerClass);

        $this->singleton($checkerClass);

        $this->autowireWhitespaceAwareFixer($checkerClass);

        if (is_a($checkerClass, FixerInterface::class, true)) {
            Assert::isAOf($checkerClass, ConfigurableFixerInterface::class);
            $this->extend($checkerClass, static function (ConfigurableFixerInterface $configurableFixer) use (
                $configuration
            ): ConfigurableFixerInterface {
                $configurableFixer->configure($configuration);
                return $configurableFixer;
            });
        }

        if (is_a($checkerClass, Sniff::class, true)) {
            $this->extend($checkerClass, static function (Sniff $sniff) use ($configuration): Sniff {
                foreach ($configuration as $propertyName => $value) {
                    Assert::propertyExists($sniff, $propertyName);
                    $sniff->{$propertyName} = $value;
                }

                return $sniff;
            });
        }
    }

    /**
     * @param array<class-string<Sniff|FixerInterface>, mixed[]> $rulesWithConfiguration
     */
    public function rulesWithConfiguration(array $rulesWithConfiguration): void
    {
        Assert::allIsArray($rulesWithConfiguration);

        foreach ($rulesWithConfiguration as $checkerClass => $configuration) {
            $this->ruleWithConfiguration($checkerClass, $configuration);
        }
    }

    /**
     * @param Option::INDENTATION_* $indentation
     */
    public function indentation(string $indentation): void
    {
        SimpleParameterProvider::setParameter(Option::INDENTATION, $indentation);
    }

    public function lineEnding(string $lineEnding): void
    {
        SimpleParameterProvider::setParameter(Option::LINE_ENDING, $lineEnding);
    }

    public function cacheDirectory(string $cacheDirectory): void
    {
        SimpleParameterProvider::setParameter(Option::CACHE_DIRECTORY, $cacheDirectory);
    }

    public function cacheNamespace(string $cacheNamespace): void
    {
        SimpleParameterProvider::setParameter(Option::CACHE_NAMESPACE, $cacheNamespace);
    }

    /**
     * @param string[] $fileExtensions
     */
    public function fileExtensions(array $fileExtensions): void
    {
        Assert::allString($fileExtensions);

        SimpleParameterProvider::addParameter(Option::FILE_EXTENSIONS, $fileExtensions);
    }

    public function parallel(int $seconds = 120, int $maxNumberOfProcess = 32, int $jobSize = 20): void
    {
        SimpleParameterProvider::setParameter(Option::PARALLEL, true);

        SimpleParameterProvider::setParameter(Option::PARALLEL_TIMEOUT_IN_SECONDS, $seconds);
        SimpleParameterProvider::setParameter(Option::PARALLEL_MAX_NUMBER_OF_PROCESSES, $maxNumberOfProcess);
        SimpleParameterProvider::setParameter(Option::PARALLEL_JOB_SIZE, $jobSize);
    }

    /**
     * @api
     */
    public function disableParallel(): void
    {
        SimpleParameterProvider::setParameter(Option::PARALLEL, false);
    }

    /**
     * @api
     */
    public function reportingRealPath(bool $absolute = true): void
    {
        SimpleParameterProvider::setParameter(Option::REPORTING_REALPATH, $absolute);
    }

    /**
     * @link https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/doc/ruleSets/index.rst
     * @param string[] $setNames
     */
    public function dynamicSets(array $setNames): void
    {
        $fixerFactory = new FixerFactory();
        $fixerFactory->registerBuiltInFixers();

        $ruleSet = new RuleSet(array_fill_keys($setNames, true));
        $fixerFactory->useRuleSet($ruleSet);

        /** @var FixerInterface $fixer */
        foreach ($fixerFactory->getFixers() as $fixer) {
            $ruleConfiguration = $ruleSet->getRuleConfiguration($fixer->getName());

            if ($ruleConfiguration === null) {
                $this->rule($fixer::class);
            } else {
                $this->ruleWithConfiguration($fixer::class, $ruleConfiguration);
            }
        }
    }

    public function import(string $setFilePath): void
    {
        $self = $this;

        $closureFilePath = require $setFilePath;
        Assert::isCallable($closureFilePath);

        $closureFilePath($self);
    }

    public function boot(): void
    {
        $removeExcludedCheckersCompilerPass = new RemoveExcludedCheckersCompilerPass();
        $removeExcludedCheckersCompilerPass->process($this);

        $removeMutualCheckersCompilerPass = new RemoveMutualCheckersCompilerPass();
        $removeMutualCheckersCompilerPass->process($this);

        $conflictingCheckersCompilerPass = new ConflictingCheckersCompilerPass();
        $conflictingCheckersCompilerPass->process($this);
    }

    /**
     * @param string $abstract
     */
    #[Override]
    public function singleton($abstract, mixed $concrete = null): void
    {
        parent::singleton($abstract, $concrete);

        foreach (self::AUTOTAG_INTERFACES as $autotagInterface) {
            if (! is_a($abstract, $autotagInterface, true)) {
                continue;
            }

            $this->tag($abstract, $autotagInterface);
        }
    }

    /**
     * @param class-string $checkerClass
     */
    private function assertCheckerClass(string $checkerClass): void
    {
        Assert::classExists($checkerClass);
        Assert::isAnyOf($checkerClass, [Sniff::class, FixerInterface::class]);
    }

    /**
     * @param string[] $checkerClasses
     */
    private function ensureCheckerClassesAreUnique(array $checkerClasses): void
    {
        // ensure all rules are registered exactly once
        $checkerClassToCount = array_count_values($checkerClasses);
        $duplicatedCheckerClassToCount = array_filter($checkerClassToCount, static fn (int $count): bool => $count > 1);

        if ($duplicatedCheckerClassToCount === []) {
            return;
        }

        $duplicatedCheckerClasses = array_flip($duplicatedCheckerClassToCount);

        $errorMessage = sprintf(
            'There are duplicated classes in $rectorConfig->rules(): "%s". Make them unique to avoid unexpected behavior.',
            implode('", "', $duplicatedCheckerClasses)
        );
        throw new InvalidArgumentException($errorMessage);
    }

    /**
     * @param class-string<FixerInterface|Sniff> $checkerClass
     */
    private function autowireWhitespaceAwareFixer(string $checkerClass): void
    {
        if (! is_a($checkerClass, WhitespacesAwareFixerInterface::class, true)) {
            return;
        }

        $this->extend(
            $checkerClass,
            static function (
                WhitespacesAwareFixerInterface $whitespacesAwareFixer,
                Container $container
            ): WhitespacesAwareFixerInterface {
                $whitespacesFixerConfig = $container->make(WhitespacesFixerConfig::class);
                $whitespacesAwareFixer->setWhitespacesConfig($whitespacesFixerConfig);

                return $whitespacesAwareFixer;
            }
        );
    }
}
