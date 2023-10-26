<?php

declare(strict_types=1);

namespace App\Command;

use App\MediaApiMigrations\ImageMigrationPostProcessor;
use App\MediaApiMigrations\MediaApiMigration;
use App\MediaApiMigrations\MigrationTableBuilder;
use App\MediaApiMigrations\UsersMigration;
use App\Model\MediaApiMigrateConfig;
use Exception;
use League\Flysystem\FilesystemException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:media-api:update',
    description: 'Migrate MediaApi'
)]
final class MediaApiUpdateMigrationCommand extends Command
{
    public function __construct(
        private readonly MediaApiMigration $mediaApiMigration,
        private readonly MigrationTableBuilder $migrationTableBuilder,
        private readonly UsersMigration $usersMigration,
        private readonly ImageMigrationPostProcessor $postProcessor
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     * @throws FilesystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fromId = $this->migrationTableBuilder->updateDeltaTable();
        $config = new MediaApiMigrateConfig(
            fromId: $fromId,
            toId: null,
            dropMigrationTable: false,
            stages: [MediaApiMigrateConfig::STAGE_MIGRATE_USERS, MediaApiMigrateConfig::STAGE_MIGRATE_IMAGES],
        );

        $this->usersMigration->migrate($config);
        $this->mediaApiMigration->migrate($config);
        $this->postProcessor->postProcess($config);

        return Command::SUCCESS;
    }
}
