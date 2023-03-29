<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use App\DamMigrations\AdmUserMigrations;
use App\DamMigrations\AssetAudioFreeMigrations;
use App\DamMigrations\AssetAudioPremiumMigrations;
use App\DamMigrations\AssetImageMigrations;
use App\DamMigrations\AssetVideoMigrations;
use App\DamMigrations\AudioCategoryMigrations;
use App\DamMigrations\AuthorMigrations;
use App\DamMigrations\KeywordMigrations;
use App\DamMigrations\LegacyDamPodcastMigrations;
use App\DamMigrations\UgcLicenceMigrations;
use App\DamMigrations\UgcUserMigrations;
use App\DamMigrations\VideoShowMigrations;
use App\MediaApiMigrations\ImageMigrationPostProcessor;
use App\MediaApiMigrations\MediaApiFileCopy;
use App\MediaApiMigrations\MediaApiMigration;
use App\MediaApiMigrations\MigrationTableBuilder;
use App\MediaApiMigrations\PoiToRoiTransformer;
use App\MediaApiMigrations\UsersMigration;
use App\Model\MediaApiMigrateConfig;
use App\Model\MediaApiMigrationIteratorConfig;
use App\Model\MigrateConfig;
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
