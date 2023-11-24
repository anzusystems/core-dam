<?php

declare(strict_types=1);

namespace App\Fs1Migration;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use App\App;
use App\MediaApiMigrations\ConnectionDecorator;
use App\Model\Fs1ApiMigrateConfig;
use App\Model\Fs1Migration\VideoMigrationDto;
use App\Model\MediaApiMigrateConfig;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Generator;

final class MigrationTableIterator
{
    use OutputUtilTrait;

    private readonly ConnectionDecorator $damMediaApiMigConnectionDecorator;

    public function __construct(
        private readonly Connection $damMediaApiMigConnection,
    ) {
    }

    /**
     * @throws Exception
     *
     * @return Generator<int, VideoMigrationDto>
     */
    public function iterate(Fs1ApiMigrateConfig $config): Generator
    {
        $lastId = null;
        $rows = $this->getMigrationItem($lastId, $config)->fetchAllAssociative();
        while (false === empty($rows)) {
            foreach ($rows as $row) {
                $lastId = $row['media_id'];

                yield VideoMigrationDto::createdFromArray($row);
            }

            $rows = $this->getMigrationItem($lastId, $config)->fetchAllAssociative();
        }
    }


    /**
     * @throws Exception
     */
    public function getCount(Fs1ApiMigrateConfig $config): int
    {
        $res = $this->damMediaApiMigConnection->fetchOne(
            'SELECT count(media_id) FROM dam_fs1_mig WHERE migration_status = :status',
            [
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
    private function getMigrationItem(?int $fromId, Fs1ApiMigrateConfig $config): Result
    {
        $params = [
            'status' => $config->getStatus(),
            'fromId' => $fromId ?? App::ZERO,
        ];
        $extraCondition = '';

        if (false === empty($config->getShowId())) {
            $params['showId'] = $config->getShowId();
            $extraCondition = ' AND show_uuid = :showId';
        }

        return $this->damMediaApiMigConnection->executeQuery(
            '
            SELECT
                media_id,
                asset_id ,
                title,
                pub_date ,
                created_at ,
                modified_at ,
                rubric_id ,
                created_by_id ,
                rubric_title,
                show_id ,
                show_title,
                authors ,
                keywords ,
                yt_id ,
                jw_id ,
                media_url,
                article_url,
                dir,
                file_path,
                file_path_hd,
                file_path_image,
                author_ids,
                keyword_ids,
                show_uuid,
                episode_uuid,
                category_uuid
            FROM dam_fs1_mig
                WHERE media_id > :fromId and migration_status = :status ' . $extraCondition . '
            LIMIT 100
        ',
            $params
        );
    }
}
