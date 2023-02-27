<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\Elasticsearch\IndexBuilder;
use AnzuSystems\CoreDamBundle\Elasticsearch\IndexManager;
use AnzuSystems\CoreDamBundle\Elasticsearch\RebuildIndexConfig;
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
    private const UGC_OPT = 'ugc';

    public function __construct(
        private readonly IndexBuilder $indexBuilder,
        private readonly RefreshAssetFilePropertiesCommand $refreshAssetFilePropertiesCommand,
        private readonly AssetAudioFreeMigrations $assetAudioFreeMigrations,
        private readonly AssetAudioPremiumMigrations $assetAudioPremiumMigrations,
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

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $migrateConfig = new MigrateConfig(
            ugc: false,
        );

        $this->assetAudioPremiumMigrations->migrate($migrateConfig);
        $this->assetAudioFreeMigrations->migrate($migrateConfig);

        $this->refreshAssetFilePropertiesCommand->updateExisting(AssetType::Audio);

        $this->indexBuilder->rebuildIndex(
            new RebuildIndexConfig('asset', 'cms', '', '', false, 10)
        );

        return Command::SUCCESS;
    }
}
