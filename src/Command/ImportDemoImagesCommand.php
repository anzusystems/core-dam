<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\DataFixtures\Provider\FixtureImageProvider;
use AnzuSystems\CoreDamBundle\Domain\Configuration\ConfigurationProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:image-fixtures:import',
    description: 'Import fixture images.'
)]
final class ImportDemoImagesCommand extends Command
{
    public function __construct(
        private readonly FixtureImageProvider $fixtureImageProvider,
        private readonly ConfigurationProvider $configurationProvider,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fixtureImageProvider->loadFixtures(
            $this->configurationProvider->getDefaultAssetLicence()
        );

        return Command::SUCCESS;
    }
}
