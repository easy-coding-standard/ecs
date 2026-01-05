<?php

declare (strict_types=1);
/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace PhpCsFixer\Console\Command;

use PhpCsFixer\Config;
use PhpCsFixer\ConfigInterface;
use PhpCsFixer\Console\ConfigurationResolver;
use PhpCsFixer\ToolInfoInterface;
use ECSPrefix202601\Symfony\Component\Console\Attribute\AsCommand;
use ECSPrefix202601\Symfony\Component\Console\Command\Command;
use ECSPrefix202601\Symfony\Component\Console\Input\InputInterface;
use ECSPrefix202601\Symfony\Component\Console\Input\InputOption;
use ECSPrefix202601\Symfony\Component\Console\Output\OutputInterface;
use ECSPrefix202601\Symfony\Component\Filesystem\Path;
/**
 * @author Markus Staab <markus.staab@redaxo.org>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ListFilesCommand extends Command
{
    /** @TODO PHP 8.0 - remove the property */
    protected static $defaultName = 'list-files';
    /** @TODO PHP 8.0 - remove the property */
    protected static $defaultDescription = 'List all files being fixed by the given config.';
    /**
     * @var \PhpCsFixer\ConfigInterface
     */
    private $defaultConfig;
    /**
     * @var \PhpCsFixer\ToolInfoInterface
     */
    private $toolInfo;
    public function __construct(ToolInfoInterface $toolInfo)
    {
        parent::__construct();
        $this->defaultConfig = new Config();
        $this->toolInfo = $toolInfo;
    }
    protected function configure() : void
    {
        $this->setDefinition([new InputOption('config', '', InputOption::VALUE_REQUIRED, 'The path to a .php-cs-fixer.php file.')]);
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $passedConfig = $input->getOption('config');
        $cwd = \getcwd();
        \assert(\false !== $cwd);
        $resolver = new ConfigurationResolver($this->defaultConfig, ['config' => $passedConfig], $cwd, $this->toolInfo);
        $finder = $resolver->getFinder();
        foreach ($finder as $file) {
            if ($file->isFile()) {
                $relativePath = './' . Path::makeRelative($file->getRealPath(), $cwd);
                // unify directory separators across operating system
                $relativePath = \str_replace('/', \DIRECTORY_SEPARATOR, $relativePath);
                $output->writeln(\escapeshellarg($relativePath));
            }
        }
        return 0;
    }
}
