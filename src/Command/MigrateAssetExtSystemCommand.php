<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\Entity\Asset;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Exception;
use League\Flysystem\FilesystemException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:asset:migrate-ext-system',
    description: 'Migrate MediaApi'
)]
final class MigrateAssetExtSystemCommand extends Command
{
    private const int MAX_RESULTS = 1_000;

    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     * @throws FilesystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $progress = new ProgressBar($output, $this->getCount());
        $progress->setFormat(ProgressBar::FORMAT_DEBUG);
        $progress->start();

        do {
            $assets = $this->getNextBatch();

            foreach ($assets as $asset) {
                $asset->setExtSystem($asset->getLicence()->getExtSystem());
                $progress->advance();
            }

            $this->em->flush();
            $this->em->clear();
        } while (false === $assets->isEmpty());

        $progress->clear();
        $output->writeln('Done');

        return Command::SUCCESS;
    }

    /**
     * @return Collection<int, Asset>
     */
    private function getNextBatch(): Collection
    {
        return new ArrayCollection(
            $this->getQb()
                ->setMaxResults(self::MAX_RESULTS)
                ->getQuery()
                ->getResult()
        );
    }

    private function getCount(): int
    {
        return (int) $this->getQb()->select('COUNT(asset)')->getQuery()->getSingleScalarResult();
    }

    private function getQb(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->from(Asset::class, 'asset')
            ->select('asset')
            ->where('asset.extSystem is null');
    }
}
