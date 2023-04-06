<?php

declare(strict_types=1);

namespace App\DamMigrations;

use App\Model\MigrateConfig;

final class PodcastEpisodesReorderMigrations extends AbstractMigrations
{
    /**
     * @var array<string, string>
     */
    private array $idCache = [];

    public function migrate(MigrateConfig $migrateConfig): void
    {
        $query = $this->defaultConnection->executeQuery('select id from podcast');

        $this->outputUtil->info('Reorder episodes');
        $progressBar = $this->outputUtil->createProgressBar();
        $progressBar->start();

        while ($row = $query->fetchAssociative()) {
            $podcastId = $row['id'] ?? '';

            $episodeQuery = $this->defaultConnection->executeQuery(
                'select id, position from podcast_episode where podcast_id = :podcastId order by created_at ASC',
                [
                    'podcastId' => $podcastId,
                ]
            );

            $i = 0;
            while ($episodeRow = $episodeQuery->fetchAssociative()) {
                $this->defaultConnection->update(
                    'podcast_episode',
                    [
                        'position' => $i,
                    ],
                    [
                        'id' => $episodeRow['id'] ?? '',
                    ]
                );

                $i++;

                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->outputUtil->writeln('');
    }
}
