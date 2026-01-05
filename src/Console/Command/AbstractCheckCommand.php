<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Console\Command;

use ECSPrefix202601\Symfony\Component\Console\Command\Command;
use ECSPrefix202601\Symfony\Component\Console\Input\InputArgument;
use ECSPrefix202601\Symfony\Component\Console\Input\InputOption;
use Symplify\EasyCodingStandard\Console\Output\ConsoleOutputFormatter;
use Symplify\EasyCodingStandard\ValueObject\Option;
abstract class AbstractCheckCommand extends Command
{
    protected function configure() : void
    {
        $this->addArgument(
            Option::PATHS,
            // optional is on purpose here, since path from ecs.php can se used
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'The path(s) to be checked.'
        );
        $this->addOption(Option::FIX, null, null, 'Fix found violations.');
        $this->addOption(Option::CONFIG, 'c', InputOption::VALUE_REQUIRED, 'Path to config file');
        $this->addOption(Option::CLEAR_CACHE, null, null, 'Clear cache for already checked files.');
        $this->addOption(Option::NO_PROGRESS_BAR, null, InputOption::VALUE_NONE, 'Hide progress bar. Useful e.g. for nicer CI output.');
        $this->addOption(Option::NO_ERROR_TABLE, null, InputOption::VALUE_NONE, 'Hide error table. Useful e.g. for fast check of error count.');
        $this->addOption(Option::NO_DIFFS, null, InputOption::VALUE_NONE, 'Hide diffs of changed files. Useful e.g. for nicer CI output.');
        $this->addOption(Option::OUTPUT_FORMAT, null, InputOption::VALUE_REQUIRED, 'Select output format', ConsoleOutputFormatter::getName());
        $this->addOption(Option::MEMORY_LIMIT, null, InputOption::VALUE_REQUIRED, 'Memory limit for check');
        // for parallel run
        $this->addOption(Option::PARALLEL_PORT, null, InputOption::VALUE_REQUIRED);
        $this->addOption(Option::PARALLEL_IDENTIFIER, null, InputOption::VALUE_REQUIRED);
    }
}
