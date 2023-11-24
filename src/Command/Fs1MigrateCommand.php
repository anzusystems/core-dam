<?php

declare(strict_types=1);

namespace App\Command;

use App\Fs1Migration\JwVideoDistribution;
use App\Fs1Migration\MigrationTableBuilder;
use App\Fs1Migration\VideoMigrator;
use App\Fs1Migration\VideoShowCleaner;
use App\Model\Fs1ApiMigrateConfig;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:fs1:migrate',
    description: 'Migrate MediaApi'
)]
final class Fs1MigrateCommand extends Command
{
    public function __construct(
        private MigrationTableBuilder $migrationTableBuilder,
        private VideoMigrator $videoMigrator,
        private VideoShowCleaner $videoShowCleaner,
        private JwVideoDistribution $jwVideoDistribution,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addOption(
            name: Fs1ApiMigrateConfig::DROP_OPTION,
            shortcut: 'd',
            mode: InputOption::VALUE_NONE,
        );
        $this->addOption(
            name: Fs1ApiMigrateConfig::CLEAN_OPTION,
            shortcut: 'c',
            mode: InputOption::VALUE_NONE,
        );
        $this->addOption(
            name: Fs1ApiMigrateConfig::MIGRATE_OPTION,
            shortcut: 'm',
            mode: InputOption::VALUE_NONE,
        );
        $this->addOption(
            name: Fs1ApiMigrateConfig::JW_DISTRIBUTE_OPTION,
            shortcut: 'j',
            mode: InputOption::VALUE_NONE,
        );
        $this->addOption(
            name: Fs1ApiMigrateConfig::STATUS_OPTION,
            shortcut: 's',
            mode: InputOption::VALUE_REQUIRED,
        );
        $this->addOption(
            name: Fs1ApiMigrateConfig::SHOW_ID_OPTION,
            shortcut: 'w',
            mode: InputOption::VALUE_REQUIRED,
        );
        $this->addOption(
            name: Fs1ApiMigrateConfig::LIMIT_OPTION,
            shortcut: 'l',
            mode: InputOption::VALUE_REQUIRED,
        );
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = Fs1ApiMigrateConfig::createFromInput($input);

        if ($config->isDrop()) {
            $this->migrationTableBuilder->buildTable($config);
        }
        if ($config->isClean()) {
            $this->videoShowCleaner->clean();
        }
        if ($config->isMigrate()) {
            $this->videoMigrator->migrate($config);
        }
        if ($config->isJwDistribute()) {
            $this->jwVideoDistribution->distribute($config);
        }

        return Command::SUCCESS;
    }
}
