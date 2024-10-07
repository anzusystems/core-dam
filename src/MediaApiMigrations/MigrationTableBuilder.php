<?php

declare(strict_types=1);

namespace App\MediaApiMigrations;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use App\Domain\Image\MediaApi\LicenceProvider;
use App\Model\MediaApiMigrateConfig;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;

final class MigrationTableBuilder
{
    use OutputUtilTrait;

    public const int CMS_EXT_ID = 1;
    private const int BATCH_LIMIT = 200;
    private const int CLEAR_CAHCE_BATCH = 5_000;

    private readonly ConnectionDecorator $damMediaApiMigConnectionDecorator;

    public function __construct(
        private readonly PoiToRoiTransformer $transformer,
        private readonly Connection $mediaApiConnection,
        private readonly Connection $damMediaApiMigConnection,
        private readonly KeywordProvider $keywordProvider,
        private readonly AuthorProvider $authorProvider,
    ) {
        $this->damMediaApiMigConnectionDecorator = new ConnectionDecorator($damMediaApiMigConnection);
    }

    public function updateDeltaTable(): ?int
    {
        $lastId = $this->getLasMediaIdFromDelta();
        $this->outputUtil->info(sprintf('Populate delta table from id %s', $lastId));
        $this->populate($lastId);

        return $lastId;
    }

    /**
     * @throws Exception
     */
    public function buildTable(MediaApiMigrateConfig $config): void
    {
        if (false === $config->allowBuildTableStage()) {
            return;
        }

        if ($config->isDropMigrationTable()) {
            $this->dropTable();
            $this->createTable();
        }

        $this->populate($config->getFromId(), $config->getToId());
    }

    private function populate(int $fromId = null, int $toId = null): void
    {
        $progress = $this->outputUtil->createProgressBar($this->getMediaApiImagesCount($fromId, $toId));
        $progress->setFormat('debug');
        $progress->start();

        $lastId = $fromId;
        $i = 0;
        do {
            $rows = $this->getMediaApiImages($lastId, $toId)->fetchAllAssociative();
            foreach ($rows as $row) {
                $i++;
                $progress->advance();
                $lastId = $row['id_image'];

                $this->insertToDelta($row);
            }

            $this->damMediaApiMigConnectionDecorator->flush();
            if (0 === $i % self::CLEAR_CAHCE_BATCH) {
                $this->keywordProvider->clearCache();
                $this->authorProvider->clearCache();
            }
        } while (self::BATCH_LIMIT === count($rows));

        $progress->finish();
        $this->outputUtil->writeln('');
        $this->outputUtil->info('Delta table populated');
    }

    private function insertToDelta(array $row): void
    {
        $this->getKeywords($row);
        $this->damMediaApiMigConnectionDecorator->prepareBulkInsert(
            'dam_media_api_mig',
            [
                'asset_id' => '',
                'main_file_id' => '',
                'media_api_id' => $row['id_image'],
                'source_url' => $row['source_url'] ?? '',
                'description' => $row['description'] ?? '',
                'author_name' => $row['author'] ?? '',
                'author_id' => $this->getAuthorId($row),
                'keywords' => json_encode($this->getKeywords($row)),
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'updated_by' => $row['id_central_user_updated'],
                'created_by' => $row['id_central_user_created'],
                'file_path' => $row['root_dir'] . $row['path'],
                'focus_x' => $row['focus_x'],
                'focus_y' => $row['focus_y'],
                'migration_status' => MediaApiMigration::STATUS_WAITING,
            ],
            ['media_api_id = new_row.media_api_id']
        );
    }

    private function getLasMediaIdFromDelta(): ?int
    {
        $res = $this->damMediaApiMigConnection->fetchOne(
            'SELECT max(media_api_id) FROM dam_media_api_mig'
        );

        if (is_numeric($res)) {
            return (int) $res;
        }

        return null;
    }

    private function getKeywords(array $row): array
    {
        if (empty($row['keywords'])) {
            return [];
        }

        $keywords = explode(', ', $row['keywords']);

        return array_map(
            fn (string $keyword): string => $this->keywordProvider->getKeyword(
                trim($keyword),
                self::CMS_EXT_ID
            ),
            $keywords
        );
    }

    private function getAuthorId(array $row): string
    {
        if (
            isset($row['author']) &&
            false === empty($row['author'])
        ) {
            return $this->authorProvider->getAuthor($row['author'], self::CMS_EXT_ID);
        }

        return '';
    }

    private function dropTable(): void
    {
        $this->damMediaApiMigConnection->executeQuery('DROP TABLE IF EXISTS dam_media_api_mig');
        $this->outputUtil->info('Table dropped');
    }

    /**
     * @throws Exception
     */
    private function createTable(): void
    {
        $sql = '
        CREATE TABLE IF NOT EXISTS dam_media_api_mig(
            media_api_id int unsigned auto_increment primary key,
            asset_id char(36) not null default \'\',
            main_file_id char(36) not null default \'\',
            source_url varchar(4096) not null default \'\',
            description varchar(500) not null default \'\',
            author_name VARCHAR(255) NOT NULL DEFAULT \'\',
            author_id char(36) default null,
            keywords json not null,
            created_at datetime default null,
            updated_at datetime default null,
            focus_x int unsigned default null,
            focus_y int unsigned default null,
            updated_by int unsigned not null,
            created_by int unsigned not null,
            file_path varchar(255) not null default \'\',
            migration_status varchar(32) not null,
            fail_reason varchar(32) not null default \'\',
            output_log varchar(256) not null default \'\'
        );';

        $this->damMediaApiMigConnection->executeQuery($sql);

        $this->damMediaApiMigConnection->executeQuery(
            'create index  IDX_MAIN_FILE on dam_media_api_mig(main_file_id);'
        );
        $this->damMediaApiMigConnection->executeQuery(
            'create index IDX_STATUS on dam_media_api_mig(migration_status);'
        );

        $this->outputUtil->info('Table created');
    }

    /**
     * @throws Exception
     */
    private function getMediaApiImages(?int $fromId, ?int $toId): Result
    {
        return $this->mediaApiConnection->executeQuery(
            '
            SELECT
                img.id_image,
                img.path,
                img.description,
                img.author,
                img.keywords,
                img.created_at,
                img.updated_at,
                img.id_central_user_created,
                img.id_central_user_updated,
                img.id_licence,
                img.id_stock,
                img.id_status,
                img.id_visibility,
                img.revision,
                img.focus_x,
                img.focus_y,
                img.is_animation,
                img.source_url,
                img.from_migration,
                stock.root_dir
            FROM mediaapi_image img
            INNER JOIN mediaapi_stock stock ON img.id_stock = stock.id_stock
            WHERE img.id_image > :fromId and img.id_image <= :toId 
            AND anzu_dam_uuid is null
            AND img.id_stock in (:stocks)
            AND img.id_status = 1 -- do not migrate removed images
            ORDER BY img.id_image ASC
            LIMIT ' . self::BATCH_LIMIT,
            [
                'fromId' => $fromId ?? 0,
                'toId' => $toId ?? MediaApiMigrateConfig::MAX_INT_SIZE,
                'stocks' => array_keys(LicenceProvider::LICENCE_MAP),
            ],
            [
                'stocks' => ArrayParameterType::INTEGER,
            ]
        );
    }

    /**
     * @throws Exception
     */
    private function getMediaApiImagesCount(?int $fromId, ?int $toId): int
    {
        $res = $this->mediaApiConnection->fetchOne(
            'SELECT count(id_image) FROM mediaapi_image WHERE id_image > :fromId AND id_image <= :toId AND anzu_dam_uuid is null AND id_stock in (:stocks) AND id_status = 1',
            [
                'fromId' => $fromId ?? 0,
                'toId' => $toId ?? MediaApiMigrateConfig::MAX_INT_SIZE,
                'stocks' => array_keys(LicenceProvider::LICENCE_MAP),
            ],
            [
                'stocks' => ArrayParameterType::INTEGER,
            ]
        );

        if (is_numeric($res)) {
            return (int) $res;
        }

        return 0;
    }
}
