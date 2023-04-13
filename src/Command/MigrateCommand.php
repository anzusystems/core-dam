<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use App\DamMigrations\UgcAssetImageMigrations;
use App\DamMigrations\UgcLicenceMigrations;
use App\DamMigrations\UgcUserMigrations;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:migrate',
    description: 'Create mandatory users.'
)]
final class MigrateCommand extends Command
{
    use OutputUtilTrait;

    public function __construct(
        private readonly UgcLicenceMigrations $ugcLicenceMigrations,
        private readonly UgcUserMigrations $ugcUserMigrations,
        private readonly UgcAssetImageMigrations $ugcAssetImageMigrations,
    ) {
        parent::__construct();
    }

    /**B
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ugcLicenceMigrations->migrate();
        $this->ugcUserMigrations->migrate();

        $this->outputUtil->info('Migrate Images');
        $this->ugcAssetImageMigrations->migrate();

        return Command::SUCCESS;
    }
}
