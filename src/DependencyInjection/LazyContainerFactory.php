<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\DependencyInjection;

use ECSPrefix202605\Illuminate\Container\Container;
use PHP_CodeSniffer\Fixer;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
use PhpCsFixer\Differ\DifferInterface;
use PhpCsFixer\Differ\UnifiedDiffer;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\WhitespacesFixerConfig;
use ECSPrefix202605\SebastianBergmann\Diff\Parser as DiffParser;
use ECSPrefix202605\Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\EasyCodingStandard\Application\SingleFileProcessor;
use Symplify\EasyCodingStandard\Caching\Cache;
use Symplify\EasyCodingStandard\Caching\CacheFactory;
use Symplify\EasyCodingStandard\Caching\ChangedFilesDetector;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\Console\Output\CheckstyleOutputFormatter;
use Symplify\EasyCodingStandard\Console\Output\ConsoleOutputFormatter;
use Symplify\EasyCodingStandard\Console\Output\GitlabOutputFormatter;
use Symplify\EasyCodingStandard\Console\Output\JsonOutputFormatter;
use Symplify\EasyCodingStandard\Console\Output\JUnitOutputFormatter;
use Symplify\EasyCodingStandard\Console\Output\OutputFormatterCollector;
use Symplify\EasyCodingStandard\Console\Style\EasyCodingStandardStyle;
use Symplify\EasyCodingStandard\Console\Style\EasyCodingStandardStyleFactory;
use Symplify\EasyCodingStandard\Console\Style\SymfonyStyleFactory;
use Symplify\EasyCodingStandard\Contract\Console\Output\OutputFormatterInterface;
use Symplify\EasyCodingStandard\FixerRunner\Application\FixerFileProcessor;
use Symplify\EasyCodingStandard\FixerRunner\WhitespacesFixerConfigFactory;
use Symplify\EasyCodingStandard\Parallel\Application\ParallelFileProcessor;
use Symplify\EasyCodingStandard\Skipper\Skipper\Skipper;
use Symplify\EasyCodingStandard\Skipper\Skipper\SkipSkipper;
use Symplify\EasyCodingStandard\SniffRunner\Application\SniffFileProcessor;
use Symplify\EasyCodingStandard\SniffRunner\DataCollector\SniffMetadataCollector;
use ECSPrefix202605\Webmozart\Assert\Assert;
final class LazyContainerFactory
{
    /**
     * @param string[] $configFiles
     */
    public function create(array $configFiles = []) : ECSConfig
    {
        $this->loadPHPCodeSnifferConstants();
        $ecsConfig = new ECSConfig();
        // make sure these services have shared instance created just once, as use setters throughout the project
        $ecsConfig->singleton(ChangedFilesDetector::class);
        $ecsConfig->singleton(SniffMetadataCollector::class);
        $ecsConfig->singleton(SingleFileProcessor::class);
        $ecsConfig->singleton(ParallelFileProcessor::class);
        $ecsConfig->singleton(Skipper::class);
        $ecsConfig->singleton(SkipSkipper::class);
        // console
        $ecsConfig->singleton(EasyCodingStandardStyle::class, static function (Container $container) : EasyCodingStandardStyle {
            /** @var EasyCodingStandardStyleFactory $easyCodingStandardStyleFactory */
            $easyCodingStandardStyleFactory = $container->make(EasyCodingStandardStyleFactory::class);
            return $easyCodingStandardStyleFactory->create();
        });
        $ecsConfig->singleton(SymfonyStyle::class, \Closure::fromCallable([SymfonyStyleFactory::class, 'create']));
        $ecsConfig->singleton(Fixer::class);
        // whitespace
        $ecsConfig->singleton(WhitespacesFixerConfig::class, static function () : WhitespacesFixerConfig {
            $whitespacesFixerConfigFactory = new WhitespacesFixerConfigFactory();
            return $whitespacesFixerConfigFactory->create();
        });
        // caching
        $ecsConfig->singleton(Cache::class, static function (Container $container) : Cache {
            /** @var CacheFactory $cacheFactory */
            $cacheFactory = $container->make(CacheFactory::class);
            return $cacheFactory->create();
        });
        // diffing
        $ecsConfig->singleton(DiffParser::class);
        // output
        $ecsConfig->singleton(GitlabOutputFormatter::class);
        $ecsConfig->singleton(CheckstyleOutputFormatter::class);
        $ecsConfig->singleton(ConsoleOutputFormatter::class);
        $ecsConfig->singleton(JsonOutputFormatter::class);
        $ecsConfig->singleton(JUnitOutputFormatter::class);
        $ecsConfig->singleton(OutputFormatterCollector::class);
        $ecsConfig->when(OutputFormatterCollector::class)->needs('$outputFormatters')->giveTagged(OutputFormatterInterface::class);
        $ecsConfig->singleton(DifferInterface::class, static function () : DifferInterface {
            return new UnifiedDiffer();
        });
        // @see https://gist.github.com/pionl/01c40225ceeed8b136306fdd96b5dabd
        $ecsConfig->singleton(FixerFileProcessor::class);
        $ecsConfig->when(FixerFileProcessor::class)->needs('$fixers')->giveTagged(FixerInterface::class);
        $ecsConfig->singleton(SniffFileProcessor::class);
        $ecsConfig->when(SniffFileProcessor::class)->needs('$sniffs')->giveTagged(Sniff::class);
        // load default config first
        $configFiles = \array_merge([__DIR__ . '/../../config/config.php'], $configFiles);
        foreach ($configFiles as $configFile) {
            $configClosure = (require $configFile);
            Assert::isCallable($configClosure);
            $configClosure($ecsConfig);
        }
        return $ecsConfig;
    }
    /**
     * These are require for PHP_CodeSniffer to run
     */
    private function loadPHPCodeSnifferConstants() : void
    {
        if (!\defined('PHP_CODESNIFFER_VERBOSITY')) {
            // initialize token with INT type, otherwise php-cs-fixer and php-parser breaks
            if (!\defined('T_MATCH')) {
                \define('T_MATCH', 5000);
            }
            if (!\defined('T_READONLY')) {
                \define('T_READONLY', 5010);
            }
            if (!\defined('T_ENUM')) {
                \define('T_ENUM', 5015);
            }
            if (!\defined('T_NULLSAFE_OBJECT_OPERATOR')) {
                \define('T_NULLSAFE_OBJECT_OPERATOR', 5020);
            }
            // for PHP_CodeSniffer
            \define('PHP_CODESNIFFER_CBF', \false);
            \define('PHP_CODESNIFFER_VERBOSITY', 0);
            new Tokens();
        }
    }
}
