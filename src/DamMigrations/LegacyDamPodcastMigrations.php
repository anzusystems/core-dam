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
        $this->outputUtil->info('Importing podcasts');
        $progressBar = $this->outputUtil->createProgressBar();

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
            'anzu_id' => 'string',
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
                'id' => $row['anzu_id'],
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

    private function getArtemisPodcasts(): Result
    {
        return $this->artemisConnection->executeQuery(
            '
            SELECT 
                id_media_channel,
                id_section,
                anzu_id,
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
}
