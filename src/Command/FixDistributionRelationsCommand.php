<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\CoreDamBundle\Repository\AssetFileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:distribution:fix-relations',
    description: 'Fix distribution relations'
)]
final class FixDistributionRelationsCommand extends Command
{
    private const int MAX_RESULTS = 1_000;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AssetFileRepository $assetFileRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $progress = new ProgressBar($output, $this->getCount());
        $progress->setFormat(ProgressBar::FORMAT_DEBUG);
        $progress->start();

        $lastId = null;
        do {
            $distributions = $this->getNextBatch($lastId);

            foreach ($distributions as $distribution) {
                $assetFile = $this->assetFileRepository->find($distribution->getAssetFileId());
                $distribution->setAssetFile(null);

                if ($assetFile instanceof AssetFile) {
                    $distribution->setAssetFile($assetFile);
                    $distribution->setAsset($assetFile->getAsset());
                }

                $progress->advance();
                $lastId = $distribution->getId();
            }

            $this->em->flush();
            $this->em->clear();
        } while (false === $distributions->isEmpty());

        $progress->clear();
        $output->writeln('Done');

        return Command::SUCCESS;
    }

    /**
     * @return Collection<int, Distribution>
     */
    private function getNextBatch(?string $lastId = null): Collection
    {
        return new ArrayCollection(
            $this->getQb($lastId)
                ->setMaxResults(self::MAX_RESULTS)
                ->getQuery()
                ->getResult()
        );
    }

    private function getCount(): int
    {
        return (int) $this->getQb()->select('COUNT(distribution)')->getQuery()->getSingleScalarResult();
    }

    private function getQb(?string $lastId = null): QueryBuilder
    {
        $qb = $this->em->createQueryBuilder()
            ->from(Distribution::class, 'distribution')
            ->select('distribution')
            ->where('distribution.assetFile is null')
            ->orderBy('distribution.id', 'ASC')
        ;

        if ($lastId) {
            $qb->andWhere('distribution.id > :lastId')
                ->setParameter('lastId', $lastId)
            ;
        }

        return $qb;
    }
}
