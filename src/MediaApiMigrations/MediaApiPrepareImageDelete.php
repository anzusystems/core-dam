<?php

declare(strict_types=1);

namespace App\MediaApiMigrations;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use League\Flysystem\FilesystemException;
use Symfony\Component\Uid\Uuid;

final class MediaApiPrepareImageDelete
{
    use OutputUtilTrait;

    public const int LIMIT = 1_000;

    private ?Statement $mediaSelectImageStatement = null;
    private ?Statement $mediaSelectImageByIdStatement = null;
    private readonly ConnectionDecorator $damMediaApiMigConnectionDecorator;

    public function __construct(
        private readonly KeywordProvider $keywordProvider,
        private readonly AuthorProvider $authorProvider,
        private readonly Connection $mediaApiConnection,
        private readonly Connection $defaultConnection,
        private readonly Connection $damMediaApiMigConnection,
    ) {
        $this->damMediaApiMigConnectionDecorator = new ConnectionDecorator($damMediaApiMigConnection);
    }

    /**
     * @throws Exception
     * @throws FilesystemException
     */
    public function migrate(): void
    {
        $this->dropTable();
        $this->createTable();

        $progress = $this->outputUtil->createProgressBar($this->getMediaApiImagesCount());

        $progress->setFormat('debug');
        $progress->start();

        $lastId = null;
        do {
            $rows = $this->getMediaApiImages($lastId)->fetchAllAssociative();

            foreach ($rows as $row) {
                $progress->advance();
                $lastId = $row['metadata_id'];

                $mediaApiIds = json_decode($row['custom_data'], true)['mediaApiIds'] ?? [];
                if (empty($mediaApiIds)) {
                    continue;
                }

                $mediaApiRow = $this->findIndMediaApiByAnzuUuid($row['image_file']);

                // anzuId does not exists in mediaapi
                if (empty($mediaApiRow)) {
                    $this->insertToDelta($mediaApiIds[0], count($mediaApiIds), $row, $mediaApiIds);
                }
            }
            $this->damMediaApiMigConnectionDecorator->flush();

        } while (self::LIMIT === count($rows));

        $this->damMediaApiMigConnectionDecorator->flush();
        $progress->finish();
    }

    private function insertToDelta(int $mediaApiId, int $count, array $row, array $mediaApiIds): void
    {
        $mediaApiData = [];
        $missingAnzuId = false;
        foreach ($mediaApiIds as $mediaApiId) {
            $mediaApiRow = $this->findIndMediaApiByMediaId($mediaApiId);
            if (empty($mediaApiRow)) {
                continue;
            }

            $missingAnzuId = null === $mediaApiRow[0]['anzu_dam_uuid'];

            $mediaApiData[] = [
                'id_image' => $mediaApiRow[0]['id_image'],
                'anzu_dam_uuid' => $missingAnzuId
                    ? null
                    : Uuid::fromBinary($mediaApiRow[0]['anzu_dam_uuid'])->toString(),
            ];
        }

        $this->damMediaApiMigConnectionDecorator->prepareBulkInsert(
            'dam_image_media_api_drop',
            [
                'asset_id' => $row['asset_id'],
                'main_file_id' => $row['image_file'],
                'possible_media_api_id_count' => $count,
                'missing_anzu_id' => $missingAnzuId ? 1 : 0,
                'ext_slug' => match ($row['ext_system_id']) {
                    1 => 'cms',
                    11 => 'scraper',
                    default => 'unsupported'
                },
                'real_media_api_id_count' => $count,
                'media_api_id' => $mediaApiId,
                'metadata' => $row['custom_data'],
                'media_api_data' => json_encode($mediaApiData),
            ],
            []
        );
    }

    private function findIndMediaApiByMediaId(int $mediaId): array
    {
        if (null === $this->mediaSelectImageByIdStatement) {
            $this->mediaSelectImageByIdStatement = $this->mediaApiConnection->prepare(
                '
                SELECT
                    img.id_image,
                    img.anzu_dam_uuid
                FROM mediaapi_image img
                WHERE img.id_image = :id'
            );
        }

        $this->mediaSelectImageByIdStatement->bindValue('id', $mediaId);

        return $this->mediaSelectImageByIdStatement->executeQuery()->fetchAllAssociative();
    }

    private function findIndMediaApiByAnzuUuid(string $anzuId): array
    {
        if (null === $this->mediaSelectImageStatement) {
            $this->mediaSelectImageStatement = $this->mediaApiConnection->prepare(
                '
                SELECT
                    img.id_image
                FROM mediaapi_image img
                WHERE img.anzu_dam_uuid = :anzu_id'
            );
        }

        $this->mediaSelectImageStatement->bindValue('anzu_id', Uuid::fromString($anzuId)->toBinary());

        return $this->mediaSelectImageStatement->executeQuery()->fetchAllAssociative();
    }

    /**
     * @throws Exception
     */
    private function getMediaApiImages(?string $fromId): Result
    {
        if ($fromId) {
            return $this->defaultConnection->executeQuery(
                '
                    select
                        afm.id metadata_id,
                        am.custom_data,
                        af.id image_file,
                        ass.id asset_id,
                        ass.ext_system_id
                    from asset_file_metadata afm
                        inner join asset_file af on afm.id = af.metadata_id
                        inner join image_file i on af.id = i.id
                        inner join asset ass on i.asset_id = ass.id
                    inner join asset_metadata am ON ass.metadata_id = am.id
                    where afm.created_by_id = 100004
                    and afm.id > :fromId
                    order by afm.id
                    LIMIT ' . self::LIMIT . '
                ',
                [
                    'fromId' => $fromId,
                ]
            );
        }
        return $this->defaultConnection->executeQuery(
            '
                select
                    afm.id metadata_id,
                    am.custom_data,
                    af.id image_file,
                    ass.id asset_id,
                    ass.ext_system_id
                from asset_file_metadata afm
                    inner join asset_file af on afm.id = af.metadata_id
                    inner join image_file i on af.id = i.id
                    inner join asset ass on i.asset_id = ass.id
                inner join asset_metadata am ON ass.metadata_id = am.id
                where afm.created_by_id = 100004
                order by afm.id
                LIMIT ' . self::LIMIT . '
            '
        );
    }

    /**
     * @throws Exception
     */
    private function getMediaApiImagesCount(): int
    {
        $res = $this->defaultConnection->fetchOne(
            '
                select
                    count(afm.id)
                from asset_file_metadata afm
                inner join asset_file af on afm.id = af.metadata_id
                inner join image_file i on af.id = i.id
                where afm.created_by_id = 100004',
        );

        if (is_numeric($res)) {
            return (int) $res;
        }

        return 0;
    }

    private function dropTable(): void
    {
        $this->damMediaApiMigConnection->executeQuery('DROP TABLE IF EXISTS dam_image_media_api_drop');
        $this->outputUtil->info('Table dropped');
    }

    /**
     * @throws Exception
     */
    private function createTable(): void
    {
        $sql = '
        CREATE TABLE IF NOT EXISTS dam_image_media_api_drop (
            id int unsigned auto_increment primary key,
            media_api_id int unsigned default 0,
            possible_media_api_id_count int unsigned default 0,
            real_media_api_id_count int unsigned default 0,
            missing_anzu_id tinyint unsigned default 0,
            main_file_id char(36) not null default \'\',
            asset_id char(36) not null default \'\',
            ext_slug char(64) not null default \'\',
            metadata json not null,
            media_api_data json not null
        );';

        $this->damMediaApiMigConnection->executeQuery($sql);

        $this->damMediaApiMigConnection->executeQuery(
            'create index  IDX_MAIN_FILE on dam_image_media_api_drop(main_file_id);'
        );
        $this->damMediaApiMigConnection->executeQuery(
            'create index IDX_MEDIA_API_ID on dam_image_media_api_drop(media_api_id);'
        );

        $this->outputUtil->info('Table created');
    }
}
