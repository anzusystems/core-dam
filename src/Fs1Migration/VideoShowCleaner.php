<?php

declare(strict_types=1);

namespace App\Fs1Migration;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Entity\VideoShowEpisode;
use AnzuSystems\CoreDamBundle\Repository\VideoShowEpisodeRepository;
use AnzuSystems\CoreDamBundle\Repository\VideoShowRepository;
use App\App;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final class VideoShowCleaner
{
    use OutputUtilTrait;

    public function __construct(
        private readonly VideoShowRepository $videoShowRepository,
        private readonly VideoShowEpisodeRepository $videoShowEpisodeRepository,
        private readonly EntityManagerInterface $manager,
    ) {
    }

    public function clean(): void
    {
        $this->outputUtil->writeln('Clean shows');
        $this->cleanVideoShows();
        $this->outputUtil->writeln('Clean episodes');
        $this->cleanVideoShowsEpisodes();
        $this->outputUtil->writeln('Reorder episodes');
        $this->reorderEpisodes();
    }

    private function cleanVideoShows(): void
    {
        $progressBar = $this->outputUtil->createProgressBar();
        $progressBar->setFormat('debug');
        $progressBar->start();

        foreach ($this->videoShowRepository->findAll() as $show) {
            $titleParts = explode('#', $show->getTexts()->getTitle());
            if (count($titleParts) > 1) {
                $show->getTexts()->setTitle($titleParts[App::ZERO]);
            }

            $progressBar->advance();
        }

        $this->manager->flush();
        $this->manager->clear();

        $progressBar->finish();
        $this->outputUtil->writeln('');
    }

    private function cleanVideoShowsEpisodes(): void
    {
        $progressBar = $this->outputUtil->createProgressBar();
        $progressBar->setFormat('debug');
        $progressBar->start();

        $lastId = null;

        do {
            $episodes = $this->getAllEpisodes($lastId);
            $episodesCount = count($episodes);
            foreach ($episodes as $showEpisode) {
                $lastId = $showEpisode->getId();

                $titleParts = explode('#', $showEpisode->getTexts()->getTitle());
                if (count($titleParts) > 1) {
                    $showEpisode->getTexts()->setTitle($titleParts[App::ZERO]);
                }

                $progressBar->advance();
            }

            $this->manager->flush();
            $this->manager->clear();
        } while ($episodesCount > App::ZERO);

        $progressBar->finish();
        $this->outputUtil->writeln('');
    }

    private function reorderEpisodes(): void
    {
        $progressBar = $this->outputUtil->createProgressBar();
        $progressBar->setFormat('debug');
        $progressBar->start();

        foreach ($this->videoShowRepository->findAll() as $show) {
            $position = 0;
            $lastId = null;
            $lastCreatedAt = null;

            do {
                $episodes = $this->getAllEpisodes(
                    lastCreatedAt: $lastCreatedAt,
                    showId: (string) $show->getId(),
                    orderBy: 's.createdAt'
                );
                $episodesCount = count($episodes);

                foreach ($episodes as $showEpisode) {
                    if ($lastId !== $showEpisode->getId()) {
                        $showEpisode->setPosition($position++);
                    }

                    $lastId = $showEpisode->getId();
                    $lastCreatedAt = $showEpisode->getCreatedAt();

                    $progressBar->advance();
                }

                $this->manager->flush();
                $this->manager->clear();
            } while ($episodesCount > App::ZERO);
        }

        $progressBar->finish();
        $this->outputUtil->writeln('');
    }

    /**
     * @return VideoShowEpisode[]
     */
    private function getAllEpisodes(
        string $lastId = null,
        DateTimeImmutable $lastCreatedAt = null,
        string $showId = null,
        string $orderBy = 's.id',
    ): array {
        $qb = $this->manager->createQueryBuilder()
            ->select('s')
            ->from(VideoShowEpisode::class, 's')
            ->orderBy($orderBy, 'ASC')
            ->setMaxResults(100);

        if ($lastId) {
            $qb->andWhere('s.id > :id')->setParameter('id', $lastId);
        }

        if ($lastCreatedAt) {
            $qb->andWhere('s.createdAt > :createdAt')->setParameter('createdAt', $lastCreatedAt->format(DateTimeImmutable::ATOM));
        }

        if ($showId) {
            $qb->andWhere('IDENTITY(s.videoShow) = :showId')->setParameter('showId', $showId);
        }

        return $qb->getQuery()->getResult();
    }
}
