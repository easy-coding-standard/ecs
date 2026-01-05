<?php

declare(strict_types=1);

// decoupled in own "*.php" file, so ECS, Rector and PHPStan works out of the box here
use PHP_CodeSniffer\Util\Tokens;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symplify\EasyCodingStandard\Console\EasyCodingStandardConsoleApplication;
use Symplify\EasyCodingStandard\Console\Style\SymfonyStyleFactory;
use Symplify\EasyCodingStandard\DependencyInjection\EasyCodingStandardContainerFactory;
use Symplify\EasyCodingStandard\DependencyInjection\LazyContainerFactory;

// performance boost
gc_disable();

define('__ECS_RUNNING__', true);

# 1. autoload
$autoloadIncluder = new ECSAutoloadIncluder();

if (file_exists(__DIR__ . '/../preload.php')) {
    require_once __DIR__ . '/../preload.php';
}

$autoloadIncluder->includeCwdVendorAutoloadIfExists();
$autoloadIncluder->loadIfNotLoadedYet(__DIR__ . '/../vendor/scoper-autoload.php');
$autoloadIncluder->autoloadProjectAutoloaderFile('/../../autoload.php');
$autoloadIncluder->includeDependencyOrRepositoryVendorAutoloadIfExists();
$autoloadIncluder->includePhpCodeSnifferAutoload();

/**
 * Inspired by https://github.com/rectorphp/rector/pull/2373/files#diff-0fc04a2bb7928cac4ae339d5a8bf67f3
 */
final class ECSAutoloadIncluder
{
    /**
     * @var string[]
     */
    private const array POSSIBLE_AUTOLOAD_PATHS = [
        // after split package
        __DIR__ . '/../vendor',
        // dependency
        __DIR__ . '/../../..',
        // monorepo
        __DIR__ . '/../../../vendor',
    ];

    /**
     * @var string[]
     */
    private array $alreadyLoadedAutoloadFiles = [];

    public function includeCwdVendorAutoloadIfExists(): void
    {
        $cwdVendorAutoload = getcwd() . '/vendor/autoload.php';
        if (! is_file($cwdVendorAutoload)) {
            return;
        }

        $this->loadIfNotLoadedYet($cwdVendorAutoload);
    }

    public function includeDependencyOrRepositoryVendorAutoloadIfExists(): void
    {
        // ECS' vendor is already loaded
        if (class_exists(LazyContainerFactory::class)) {
            return;
        }

        $devVendorAutoload = __DIR__ . '/../vendor/autoload.php';
        if (! is_file($devVendorAutoload)) {
            return;
        }

        $this->loadIfNotLoadedYet($devVendorAutoload);
    }

    public function autoloadProjectAutoloaderFile(string $file): void
    {
        $path = dirname(__DIR__) . $file;
        if (! is_file($path)) {
            return;
        }

        $this->loadIfNotLoadedYet($path);
    }

    public function includePhpCodeSnifferAutoload(): void
    {
        // 1. autoload
        foreach (self::POSSIBLE_AUTOLOAD_PATHS as $possibleAutoloadPath) {
            $possiblePhpCodeSnifferAutoloadPath = $possibleAutoloadPath . '/squizlabs/php_codesniffer/autoload.php';
            if (! is_file($possiblePhpCodeSnifferAutoloadPath)) {
                continue;
            }

            require_once $possiblePhpCodeSnifferAutoloadPath;
        }

        // initialize token with INT type, otherwise php-cs-fixer and php-parser breaks
        if (! defined('T_MATCH')) {
            define('T_MATCH', 5000);
        }

        if (! defined('T_READONLY')) {
            define('T_READONLY', 5010);
        }

        if (! defined('T_ENUM')) {
            define('T_ENUM', 5015);
        }

        if (! defined('T_NULLSAFE_OBJECT_OPERATOR')) {
            define('T_NULLSAFE_OBJECT_OPERATOR', 5020);
        }

        // for PHP_CodeSniffer
        define('PHP_CODESNIFFER_CBF', false);
        define('PHP_CODESNIFFER_VERBOSITY', 0);

        new Tokens();
    }

    public function loadIfNotLoadedYet(string $file): void
    {
        if (! file_exists($file)) {
            return;
        }

        if (in_array($file, $this->alreadyLoadedAutoloadFiles, true)) {
            return;
        }

        $realPath = realpath($file);
        if (! is_string($realPath)) {
            return;
        }

        $this->alreadyLoadedAutoloadFiles[] = $realPath;
        require_once $file;
    }
}

try {
    $input = new ArgvInput();
    $ecsContainerFactory = new EasyCodingStandardContainerFactory();
    $container = $ecsContainerFactory->createFromFromInput($input);
} catch (Throwable $throwable) {
    $symfonyStyleFactory = new SymfonyStyleFactory();
    $symfonyStyle = $symfonyStyleFactory->create();

    $symfonyStyle->error($throwable->getMessage());
    $symfonyStyle->writeln($throwable->getTraceAsString());
    exit(Command::FAILURE);
}

/** @var EasyCodingStandardConsoleApplication $application */
$application = $container->get(EasyCodingStandardConsoleApplication::class);

$statusCode = $application->run();
exit($statusCode);
