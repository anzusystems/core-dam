<?php

declare(strict_types=1);

namespace App\Command;

use App\DamMigrations\LegacyDamPodcastMigrations;
use App\DamMigrations\LicenceMigrations;
use App\DamMigrations\UserMigrations;
use Doctrine\DBAL\Connection;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Result;

#[AsCommand(
    name: 'anzu:migrate',
    description: 'Create mandatory users.'
)]
final class MigrateCommand extends Command
{
    public function __construct(
        private readonly Connection $artemisConnection,
        private readonly Connection $damLegacyConnection,
        private readonly LegacyDamPodcastMigrations $legacyDamPodcastMigrations,
        private readonly UserMigrations $userMigrations,
        private readonly LicenceMigrations $licenceMigrations,
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
//        $this->licenceMigrations->migrate();
//        $this->userMigrations->migrate();
//        $this->legacyDamPodcastMigrations->migratePodcasts(1);
        return Command::SUCCESS;
    }
}
