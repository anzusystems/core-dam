<?php

declare(strict_types=1);

namespace App\Command;

use App\DocumentMigration\DocumentMigrator;
use App\DocumentMigration\MigrationTableBuilder;
use App\DocumentMigration\MigrationTableIterator;
use App\Model\Fs1ApiMigrateConfig;
use App\Model\StaticFilesMigrateConfig;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:docs:migrate',
    description: 'Migrate MediaApi'
)]
final class StaticFilesMigrateCommand extends Command
{
    public function __construct(
        private readonly MigrationTableBuilder $builder,
        private readonly DocumentMigrator $documentMigrator,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addOption(
            name: StaticFilesMigrateConfig::DROP_OPTION,
            shortcut: 'd',
            mode: InputOption::VALUE_NONE,
        );
        $this->addOption(
            name: StaticFilesMigrateConfig::MIGRATE_OPTION,
            shortcut: 'm',
            mode: InputOption::VALUE_NONE,
        );
        $this->addOption(
            name: StaticFilesMigrateConfig::STATUS_OPTION,
            shortcut: 's',
            mode: InputOption::VALUE_REQUIRED,
        );
        $this->addOption(
            name: StaticFilesMigrateConfig::LIMIT_OPTION,
            shortcut: 'l',
            mode: InputOption::VALUE_REQUIRED,
        );
        $this->addOption(
            name: StaticFilesMigrateConfig::EXTENSION_OPTION,
            shortcut: 'x',
            mode: InputOption::VALUE_REQUIRED,
        );
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = StaticFilesMigrateConfig::createFromInput($input);

        if ($config->isDrop()) {
            $this->builder->buildTable($config);
        }
        if ($config->isMigrate()) {
            $this->documentMigrator->migrate($config);
        }

        return Command::SUCCESS;
    }
}
