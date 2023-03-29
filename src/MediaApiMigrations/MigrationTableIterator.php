<?php

declare(strict_types=1);

namespace App\MediaApiMigrations;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use App\Model\MediaApiMigrateConfig;
use App\Model\MediaApiMigration\ImageMigrationDto;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Generator;

final class MigrationTableIterator
{
    use OutputUtilTrait;

    private const CMS_EXT_ID = 1_000;

    private readonly ConnectionDecorator $damMediaApiMigConnectionDecorator;

    public function __construct(
        private readonly Connection $damMediaApiMigConnection,
    ) {
    }

    /**
     * @throws Exception
     *
     * @return Generator<int, ImageMigrationDto>
     */
    public function iterate(MediaApiMigrateConfig $config): Generator
    {
        // todo support for migration_status filter
        $lastId = $config->getFromId();
        $rows = $this->getMigrationItem($lastId, $config)->fetchAllAssociative();
        while (false === empty($rows)) {
            foreach ($rows as $row) {
                $lastId = $row['media_api_id'];

                yield ImageMigrationDto::createdFromArray($row);
            }

            $rows = $this->getMigrationItem($lastId, $config)->fetchAllAssociative();
        }
    }


    /**
     * @throws Exception
     */
    public function getCount(MediaApiMigrateConfig $config): int
    {
        $res = $this->damMediaApiMigConnection->fetchOne(
            'SELECT count(media_api_id) FROM dam_media_api_mig WHERE media_api_id > :fromId and media_api_id <= :toId and migration_status = :status',
            [
                'fromId' => $config->getFromId() ?? 0,
                'toId' => $config->getToId() ?? MediaApiMigrateConfig::MAX_INT_SIZE,
                'status' => $config->getStatus(),
            ]
        );

        if (is_numeric($res)) {
            return (int) $res;
        }

        return 0;
    }

    /**
     * @throws Exception
     */
    private function getMigrationItem(?int $fromId, MediaApiMigrateConfig $config): Result
    {
        return $this->damMediaApiMigConnection->executeQuery(
            '
            SELECT
                media_api_id,
                asset_id,
                main_file_id,
                source_url,
                description,
                author_id,
                keywords,
                created_at,
                updated_at,
                focus_x,
                focus_y,
                updated_by,
                created_by,
                file_path,
                fail_reason,
                output_log,
                migration_status
            FROM dam_media_api_mig
            WHERE media_api_id > :fromId and media_api_id <= :toId and migration_status = :status
            LIMIT 100
        ',
            [
                'fromId' => $fromId ?? 0,
                'toId' => $config->getToId() ?? MediaApiMigrateConfig::MAX_INT_SIZE,
                'status' => $config->getStatus(),
            ]
        );
    }
}
