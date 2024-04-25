<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\DataFixtures\Provider\FixtureImageProvider;
use AnzuSystems\CoreDamBundle\Domain\Configuration\ConfigurationProvider;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use League\Flysystem\FilesystemException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:image-fixtures:import',
    description: 'Import fixture images.'
)]
final class ImportDemoImagesCommand extends Command
{
    private const OPT_LICENCE_ID = 'licence-id';
    private const OPT_LICENCE_ALL_VALUE = 'all';

    public function __construct(
        private readonly FixtureImageProvider $fixtureImageProvider,
        private readonly EntityManagerInterface $entityManager,
        private readonly ConfigurationProvider $configurationProvider,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addOption(
            name: self::OPT_LICENCE_ID,
            mode: InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            description: 'Use "all" for all licences. Don\'t use the option for only default licence. Or use specific licence id.'
        );
    }

    /**
     * @throws FilesystemException
     * @throws ORMException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $licenceIds = $input->getOption(self::OPT_LICENCE_ID);
        $licenceIds = $licenceIds ?: [$this->configurationProvider->getSettings()->getDefaultAssetLicenceId()];
        if (in_array(self::OPT_LICENCE_ALL_VALUE, $licenceIds, true)) {
            $licenceIds = $this->entityManager->createQueryBuilder()
                ->select('licence.id')
                ->from(AssetLicence::class, 'licence')
                ->getQuery()
                ->getSingleColumnResult()
            ;
        }

        foreach ($licenceIds as $licenceId) {
            $assetLicence = $this->entityManager->find(
                AssetLicence::class,
                (int) $licenceId
            );
            $this->fixtureImageProvider->loadFixtures($assetLicence);
            $this->entityManager->clear();
        }

        return Command::SUCCESS;
    }
}
