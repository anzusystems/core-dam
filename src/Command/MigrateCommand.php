<?php

declare(strict_types=1);

namespace App\Command;

use App\DamMigrations\LegacyDamPodcastMigrations;
use App\DamMigrations\AdmUserMigrations;
use App\DamMigrations\UgcLicenceMigrations;
use App\DamMigrations\UgcUserMigrations;
use App\DamMigrations\AssetImageMigrations;
use App\DamMigrations\AudioCategoryMigrations;
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
        private readonly LegacyDamPodcastMigrations $legacyDamPodcastMigrations,
        private readonly UgcLicenceMigrations $ugcLicenceMigrations,
        private readonly AdmUserMigrations $admUserMigrations,
        private readonly UgcUserMigrations $ugcUserMigrations,
        private readonly AssetImageMigrations $assetImageMigrations,
        private readonly AudioCategoryMigrations $audioCategoryMigrations,
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
            ugc: (bool) $input->getOption(self::UGC_OPT),
        );

        $this->ugcLicenceMigrations->migrate($migrateConfig);
        $this->admUserMigrations->migrate($migrateConfig);
        $this->ugcUserMigrations->migrate($migrateConfig);
        $this->assetImageMigrations->migrate($migrateConfig);
        $this->legacyDamPodcastMigrations->migrate($migrateConfig);
        $this->audioCategoryMigrations->migrate($migrateConfig);

        return Command::SUCCESS;
    }
}
