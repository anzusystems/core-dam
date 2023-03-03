<?php

declare(strict_types=1);

namespace App\DamMigrations;

use AnzuSystems\CoreDamBundle\Model\Enum\PodcastImportMode;
use AnzuSystems\CoreDamBundle\Model\Enum\PodcastLastImportStatus;
use App\App;
use App\Entity\User;
use App\Model\MigrateConfig;
use DateTimeImmutable;
use Doctrine\DBAL\Result;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Uid\Uuid;

final class VideoShowMigrations extends AbstractMigrations
{
    /**
     * @var array<string, string>
     */
    private array $idCache = [];

    public function migrate(MigrateConfig $migrateConfig): void
    {
        $res = $this->getDamShows();
        $this->outputUtil->info('Importing video shows');
        $progressBar = $this->outputUtil->createProgressBar();
        $progressBar->start();
        while ($row = $res->fetchAssociative()) {
            $this->insertLegacyDamPodcast($row);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->outputUtil->writeln('');
    }

    private function insertLegacyDamPodcast(
        #[ArrayShape([
            'id' => 'integer',
            'created_by_id' => 'integer',
            'modified_by_id' => 'integer',
            'title' => 'string',
            'created_at' => 'string',
            'modified_at' => 'string',
            'type' => 'string',
        ])]
        array $row,
    ): void {
        $this->prepareBulkInsert(
            'video_show',
            [
                'id' => $this->getId($row['title']),
                'texts_title' => $row['title'],
                'created_at' => $row['created_at'],
                'modified_at' => $row['modified_at'],
                'created_by_id' => $row['created_by_id'],
                'modified_by_id' => $row['modified_by_id'],
                'licence_id' => self::CMS_LICENCE_ID,
            ],
        );
        $this->flush();
    }

    private function getDamShows(): Result
    {
        return $this->damLegacyConnection->executeQuery(
            '
            SELECT
               id, created_by_id, modified_by_id, title, created_at, modified_at, artemis_tag_id, type
            FROM video_show
            WHERE type = \'video_type\'
        '
        );
    }

    private function getId(string $title): string
    {
        if (false === isset($this->idCache[$title])) {
            $oldId = $this->defaultConnection->fetchOne(
                'SELECT id FROM video_show WHERE texts_title like ? AND licence_id = ?',
                [
                    $title . '%',
                    self::CMS_LICENCE_ID,
                ]
            );

            $this->idCache[$title] = is_string($oldId)
                ? $oldId
                : (string) Uuid::v6();
        }

        return $this->idCache[$title];
    }
}
