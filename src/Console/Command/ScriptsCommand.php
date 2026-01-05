<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Console\Command;

use Override;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\EasyCodingStandard\FileSystem\JsonFileSystem;

final class ScriptsCommand extends AbstractCheckCommand
{
    public function __construct(
        private readonly SymfonyStyle $symfonyStyle,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this->setName('scripts');
        $this->setDescription('Enhance "scripts" section in composer.json with shortcuts');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $composerJsonFilePath = getcwd() . DIRECTORY_SEPARATOR . 'composer.json';
        if (! file_exists($composerJsonFilePath)) {
            $this->symfonyStyle->error('The "composer.json" was not found.');

            return self::FAILURE;
        }

        $composerJson = JsonFileSystem::readFilePath($composerJsonFilePath);

        if (isset($composerJson['scripts']['check-cs']) && isset($composerJson['scripts']['fix-cs'])) {
            $this->symfonyStyle->warning('The scripts were already added. You can run them:');

            $this->symfonyStyle->listing(['composer check-cs', 'composer fix-cs']);

            return self::SUCCESS;
        }

        $composerJson['scripts']['check-cs'] = 'vendor/bin/ecs check --ansi';
        $composerJson['scripts']['fix-cs'] = 'vendor/bin/ecs check --fix --ansi';

        JsonFileSystem::writeFilePath($composerJsonFilePath, $composerJson);

        $this->symfonyStyle->success('Your composer.json is now extended with 2 handy scripts:');
        $this->symfonyStyle->listing(['composer check-cs', 'composer fix-cs']);

        return self::SUCCESS;
    }
}
