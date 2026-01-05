<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\DependencyInjection;

use Illuminate\Container\Container;
use Symfony\Component\Console\Input\ArgvInput;
use Symplify\EasyCodingStandard\Caching\ChangedFilesDetector;
use Symplify\EasyCodingStandard\Config\ECSConfig;

/**
 * @api
 */
final class EasyCodingStandardContainerFactory
{
    public function createFromFromInput(ArgvInput $argvInput): ECSConfig
    {
        // $easyCodingStandardKernel = new EasyCodingStandardKernel();
        $lazyContainerFactory = new LazyContainerFactory();

        $inputConfigFiles = [];
        $rootECSConfig = getcwd() . DIRECTORY_SEPARATOR . 'ecs.php';

        if ($argvInput->hasParameterOption(['--config', '-c'])) {
            $commandLineConfigFile = $argvInput->getParameterOption(['--config', '-c']);
            if (is_string($commandLineConfigFile) && file_exists($commandLineConfigFile)) {
                // must be realpath, so container builder knows the location
                $inputConfigFiles[] = (string) realpath($commandLineConfigFile);
            }
        } elseif (file_exists($rootECSConfig)) {
            $inputConfigFiles[] = $rootECSConfig;
        }

        $ecsConfig = $lazyContainerFactory->create($inputConfigFiles);
        $ecsConfig->boot();

        if ($inputConfigFiles !== []) {
            // for cache invalidation on config change
            /** @var ChangedFilesDetector $changedFilesDetector */
            $changedFilesDetector = $ecsConfig->make(ChangedFilesDetector::class);
            $changedFilesDetector->setUsedConfigs($inputConfigFiles);
        }

        return $ecsConfig;
    }
}
