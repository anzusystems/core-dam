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

final class LegacyDamPodcastMigrations extends AbstractMigrations
{
    private const NOT_MIGRATE_IDS = [99];

    /**
     * @var array<string, string>
     */
    private array $idCache = [];

    public function migrate(MigrateConfig $migrateConfig): void
    {
        $res = $this->getDamPodcasts();
        $this->outputUtil->info('Importing podcasts');
        $progressBar = $this->outputUtil->createProgressBar();
        $progressBar->start();
        while ($row = $res->fetchAssociative()) {
            $this->insertLegacyDamPodcast($row);
            $progressBar->advance();
        }

        $res = $this->getArtemisPodcasts();
        $progressBar->clear();
        $this->outputUtil->writeln('');

        $this->outputUtil->info('Importing from Artemis');
        $progressBar->start();
        while ($row = $res->fetchAssociative()) {
            if (isset($row['id_media_channel']) && in_array($row['id_media_channel'], self::NOT_MIGRATE_IDS, true)) {
                continue;
            }

            $this->insertArtemisPodcast($row);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->outputUtil->writeln('');
    }

    private function insertArtemisPodcast(
        #[ArrayShape([
            'id_media_channel' => 'integer',
            'id_section' => 'integer',
            'id_media_channel_status' => 'string',
            'id_media_service' => 'string',
            'title' => 'string',
            'description' => 'string',
            'id_image' => 'integer',
            'rss_feed' => 'string',
        ])]
        array $row,
    ): void {
        $this->prepareBulkInsert(
            'podcast',
            [
                'id' => $this->getId($row['title']),
                'texts_title' => $row['title'],
                'created_by_id' => User::ID_CONSOLE,
                'modified_by_id' => User::ID_CONSOLE,
                'created_at' => App::getAppDate()->format(DateTimeImmutable::ATOM),
                'modified_at' => App::getAppDate()->format(DateTimeImmutable::ATOM),
                'dates_import_from' => App::getAppDate()->format(DateTimeImmutable::ATOM),
                'texts_description' => $row['description'],
                'attributes_rss_url' => (string) $row['rss_feed'],
                'licence_id' => self::CMS_LICENCE_ID,
                'attributes_file_slot' => 'free',
                'attributes_last_import_status' => PodcastLastImportStatus::NotImported->toString(),
                'attributes_mode' => empty($row['rss_feed'])
                    ? PodcastImportMode::NotImport->toString()
                    : PodcastImportMode::Import->toString(),
            ],
            [
                'id = new_row.id',
                'texts_description = new_row.texts_description',
                'attributes_rss_url = new_row.attributes_rss_url',
                'attributes_mode = new_row.attributes_mode',
            ]
        );
        $this->flush();
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
            'podcast',
            [
                'id' => $this->getId($row['title']),
                'texts_title' => $row['title'],
                'created_at' => $row['created_at'],
                'modified_at' => $row['modified_at'],
                'created_by_id' => $row['created_by_id'],
                'modified_by_id' => $row['modified_by_id'],
                'dates_import_from' => App::getAppDate()->format(DateTimeImmutable::ATOM),
                'texts_description' => '',
                'attributes_rss_url' => '',
                'attributes_file_slot' => 'free',
                'licence_id' => self::CMS_LICENCE_ID,
                'attributes_last_import_status' => PodcastLastImportStatus::NotImported->toString(),
                'attributes_mode' => PodcastImportMode::NotImport->toString(),
            ],
        );
        $this->flush();
    }

    private function getArtemisPodcasts(): Result
    {
        return $this->artemisConnection->executeQuery(
            '
            SELECT 
                id_media_channel,
                id_section,
                id_media_channel_status,
                id_media_service,
                title,
                description,
                id_image,
                rss_feed
            FROM artemis_media_channel
        '
        );
    }

    private function getDamPodcasts(): Result
    {
        return $this->damLegacyConnection->executeQuery(
            '
            SELECT
               id, created_by_id, modified_by_id, title, created_at, modified_at, artemis_tag_id, type
            FROM video_show
            WHERE type = \'audio_type\'
        '
        );
    }

    private function getId(string $title): string
    {
        if (false === isset($this->idCache[$title])) {
            $oldId = $this->defaultConnection->fetchOne(
                'SELECT id FROM podcast WHERE texts_title like ? AND licence_id = ?',
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
