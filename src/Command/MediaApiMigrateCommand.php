<?php

declare(strict_types=1);

namespace App\Command;

use App\MediaApiMigrations\ImageMigrationPostProcessor;
use App\MediaApiMigrations\MediaApiFileCopy;
use App\MediaApiMigrations\MediaApiMigration;
use App\MediaApiMigrations\MigrationTableBuilder;
use App\MediaApiMigrations\UsersMigration;
use App\Model\MediaApiMigrateConfig;
use Exception;
use League\Flysystem\FilesystemException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:media-api:migrate',
    description: 'Migrate MediaApi'
)]
final class MediaApiMigrateCommand extends Command
{
    public function __construct(
        private readonly MediaApiMigration $mediaApiMigration,
        private readonly MediaApiFileCopy $mediaApiFileCopy,
        private readonly MigrationTableBuilder $migrationTableBuilder,
        private readonly UsersMigration $usersMigration,
        private readonly ImageMigrationPostProcessor $postProcessor
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addArgument(
            name: MediaApiMigrateConfig::FROM_ID_ARG,
            mode: InputArgument::OPTIONAL,
        );

        $this->addArgument(
            name: MediaApiMigrateConfig::TO_ID_ARG,
            mode: InputArgument::OPTIONAL,
        );
        $this->addOption(
            name: MediaApiMigrateConfig::DROP_MIGRATION_TABLE_OPTION,
            mode: InputOption::VALUE_NONE,
        );
        $this->addOption(
            name: MediaApiMigrateConfig::LIMIT_OPT,
            mode: InputOption::VALUE_OPTIONAL,
        );
        $this->addOption(
            name: MediaApiMigrateConfig::STAGES_OPT,
            mode: InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            default: MediaApiMigrateConfig::DEFAULT_STAGES
        );
        $this->addOption(
            name: MediaApiMigrateConfig::MEDIA_API_SOURCE_STORAGE_OPT,
            mode: InputOption::VALUE_REQUIRED,
            default: MediaApiMigrateConfig::REMOTE_STORAGE,
        );
    }

    /**
     * @throws Exception
     * @throws FilesystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = MediaApiMigrateConfig::createFromInput($input);
        $this->migrationTableBuilder->buildTable($config);
        $this->usersMigration->migrate($config);
        $this->mediaApiMigration->migrate($config);
        $this->postProcessor->postProcess($config);
        $output->writeln('');

        return Command::SUCCESS;
    }
}
