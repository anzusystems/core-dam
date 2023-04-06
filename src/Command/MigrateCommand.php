<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Elasticsearch\IndexBuilder;
use AnzuSystems\CoreDamBundle\Elasticsearch\RebuildIndexConfig;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use App\DamMigrations\AdmUserMigrations;
use App\DamMigrations\AssetAudioFreeMigrations;
use App\DamMigrations\AssetAudioPremiumMigrations;
use App\DamMigrations\AssetImageMigrations;
use App\DamMigrations\AssetLicenceMigrations;
use App\DamMigrations\AssetVideoMigrations;
use App\DamMigrations\AudioCategoryMigrations;
use App\DamMigrations\AuthorMigrations;
use App\DamMigrations\KeywordMigrations;
use App\DamMigrations\LegacyDamPodcastMigrations;
use App\DamMigrations\PodcastEpisodesReorderMigrations;
use App\DamMigrations\UgcLicenceMigrations;
use App\DamMigrations\UgcUserMigrations;
use App\DamMigrations\VideoShowMigrations;
use App\Model\MigrateConfig;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:migrate',
    description: 'Create mandatory users.'
)]
final class MigrateCommand extends Command
{
    use OutputUtilTrait;
    private const UGC_OPT = 'ugc';

    public function __construct(
        private readonly LegacyDamPodcastMigrations $legacyDamPodcastMigrations,
        private readonly UgcLicenceMigrations $ugcLicenceMigrations,
        private readonly AdmUserMigrations $admUserMigrations,
        private readonly UgcUserMigrations $ugcUserMigrations,
        private readonly AssetImageMigrations $assetImageMigrations,
        private readonly AudioCategoryMigrations $audioCategoryMigrations,
        private readonly AuthorMigrations $authorMigrations,
        private readonly KeywordMigrations $keywordMigrations,
        private readonly AssetAudioPremiumMigrations $assetAudioPremiumMigrations,
        private readonly AssetAudioFreeMigrations $assetAudioFreeMigrations,
        private readonly AssetVideoMigrations $assetVideoMigrations,
        private readonly VideoShowMigrations $videoShowMigrations,
        private readonly RefreshAssetFilePropertiesCommand $refreshAssetFilePropertiesCommand,
        private readonly PodcastEpisodesReorderMigrations $reorderMigrations,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            name: self::UGC_OPT,
            mode: InputOption::VALUE_NONE,
            description: 'Should migrate UGC content?',
        );
    }

    /**B
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $migrateConfig = new MigrateConfig(
            ugc: false,
        );

        $this->ugcLicenceMigrations->migrate($migrateConfig);
        $this->admUserMigrations->migrate($migrateConfig);
        $this->ugcUserMigrations->migrate($migrateConfig);
        $this->authorMigrations->migrate($migrateConfig);
        $this->keywordMigrations->migrate($migrateConfig);
        $this->audioCategoryMigrations->migrate($migrateConfig);
        $this->legacyDamPodcastMigrations->migrate($migrateConfig);
        $this->videoShowMigrations->migrate($migrateConfig);

        $this->outputUtil->info('Migrate Images');
        $this->assetImageMigrations->migrate($migrateConfig);
        $this->outputUtil->info('Migrate Premium Audio');
        $this->assetAudioPremiumMigrations->migrate($migrateConfig);
        $this->outputUtil->info('Migrate Free Audio');
        $this->assetAudioFreeMigrations->migrate($migrateConfig);
        $this->outputUtil->info('Migrate Video');
        $this->assetVideoMigrations->migrate($migrateConfig);

        $this->reorderMigrations->migrate($migrateConfig);

        $this->refreshAssetFilePropertiesCommand->updateExisting(AssetType::Video);
        $this->refreshAssetFilePropertiesCommand->updateExisting(AssetType::Image);
        $this->refreshAssetFilePropertiesCommand->updateExisting(AssetType::Audio);

        return Command::SUCCESS;
    }
}
