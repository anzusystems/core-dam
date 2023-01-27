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
    private const ARTEMIS_SHAPE = [
        'id_media_channel' => 'integer',
        'id_section' => 'integer',
        'id_media_channel_status' => 'string',
        'id_media_service' => 'string',
        'title' => 'string',
        'description' => 'string',
        'id_image' => 'integer',
        'rss_feed' => 'string',
    ];
    private const LEGACY_DAM_SHAPE = [
        'id' => 'integer',
        'created_by_id' => 'integer',
        'modified_by_id' => 'integer',
        'title' => 'string',
        'created_at' => 'string',
        'modified_at' => 'string',
        'type' => 'string',
    ];


    public function migrate(MigrateConfig $migrateConfig): void
    {
        $licenceId = 100_000; // TODO
        $res = $this->getDamPodcasts();

        $this->outputUtil->info('Importing from legacy Dam');
        while ($row = $res->fetchAssociative()) {
            if ($this->podcastExists($row['title'], $licenceId)) {
                continue;
            }
            $this->insertLegacyDamPodcast($row, $licenceId);
            $this->outputUtil->writeln(sprintf('Create podcast (%s)', $row['title']));
        }

        $this->outputUtil->info('Importing from Artemis');
        $res = $this->getArtemisPodcasts();
        while ($row = $res->fetchAssociative()) {
            $this->updateOrInsert($row, $licenceId);
        }
    }

    private function updateOrInsert(
        #[ArrayShape(self::ARTEMIS_SHAPE)]
        array $row,
        int $licenceId
    ): void {
        $oldId = $this->podcastExists($row['title'], $licenceId);
        if ($oldId) {
            $this->updatePodcast($oldId, $row);
            $this->outputUtil->writeln(sprintf('Update podcast (%s) id (%s)', $row['title'], $oldId));

            return;
        }

        $this->insertArtemisPodcast($row, $licenceId);
        $this->outputUtil->writeln(sprintf('Create podcast (%s)', $row['title']));
    }

    private function updatePodcast(
        string $id,
        #[ArrayShape(self::ARTEMIS_SHAPE)]
        array $row,
    ): void {
        $this->defaultConnection->update(
            'podcast',
            [
                'texts_description' => (string) $row['description'],
                'attributes_rss_url' => (string) $row['rss_feed'],
                'attributes_mode' => empty($row['rss_feed'])
                    ? PodcastImportMode::notImport->toString()
                    : PodcastImportMode::import->toString()
            ],
            [
                'id' => $id
            ]
        );
    }

    private function insertArtemisPodcast(
        #[ArrayShape(self::ARTEMIS_SHAPE)]
        array $row,
        int $licenceId
    ): void
    {
        $this->defaultConnection->insert(
            'podcast',
            [
                'id' =>  Uuid::v6(),
                'texts_title' => $row['title'],
                'created_by_id' => User::ID_CONSOLE,
                'modified_by_id' => User::ID_CONSOLE,
                'created_at' => App::getAppDate()->format(DateTimeImmutable::ATOM),
                'modified_at' => App::getAppDate()->format(DateTimeImmutable::ATOM),
                'texts_description' => $row['description'],
                'attributes_rss_url' => (string) $row['rss_feed'], // todo validate
                'licence_id' => $licenceId,
                'attributes_last_import_status' => PodcastLastImportStatus::notImported->toString(),
                'attributes_mode' => PodcastImportMode::import->toString(),
            ]
        );
    }

    private function insertLegacyDamPodcast(
        #[ArrayShape(self::LEGACY_DAM_SHAPE)]
        array $row,
        int $licenceId,
    ): void
    {
        $this->defaultConnection->insert(
            'podcast',
            [
                'id' =>  Uuid::v6(),
                'texts_title' => $row['title'],
                'created_at' => $row['created_at'],
                'modified_at' => $row['modified_at'],
                'created_by_id' => $row['created_by_id'],
                'modified_by_id' => $row['modified_by_id'],
                'texts_description' => '',
                'licence_id' => $licenceId,
                'attributes_last_import_status' => PodcastLastImportStatus::notImported->toString(),
                'attributes_mode' => PodcastImportMode::import->toString(),
            ]
        );
    }

    private function getArtemisPodcasts(): Result
    {
        return $this->artemisConnection->executeQuery('
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
        ');
    }

    private function podcastExists(string $title, int $licenceId): ?string
    {
        $res = $this->defaultConnection->fetchOne(
            'SELECT id FROM podcast WHERE texts_title like ? AND licence_id = ?',
            [
                $title . '%',
                $licenceId,
            ]
        );

        return is_string($res) ? $res : null;
    }

    private function getDamPodcasts(): Result
    {
        return $this->damLegacyConnection->executeQuery('
            SELECT
               id, created_by_id, modified_by_id, title, created_at, modified_at, artemis_tag_id, type
            FROM video_show
            WHERE type = \'audio_type\'
        ');
    }
}
