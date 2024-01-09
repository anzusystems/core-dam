<?php

declare(strict_types=1);

namespace App\DocumentMigration;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use App\App;
use App\MediaApiMigrations\ConnectionDecorator;
use App\Model\Fs1ApiMigrateConfig;
use App\Model\Fs1Migration\DocumentMigrationDto;
use App\Model\Fs1Migration\VideoMigrationDto;
use App\Model\MediaApiMigrateConfig;
use App\Model\StaticFilesMigrateConfig;
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
     * @return Generator<int, DocumentMigrationDto>
     */
    public function iterate(StaticFilesMigrateConfig $config): Generator
    {
        $lastId = null;
        $rows = $this->getMigrationItem($lastId, $config)->fetchAllAssociative();
        while (false === empty($rows)) {
            foreach ($rows as $row) {
                $lastId = $row['id'];

                yield DocumentMigrationDto::createdFromArray($row);
            }

            $rows = $this->getMigrationItem($lastId, $config)->fetchAllAssociative();
        }
    }


    /**
     * @throws Exception
     */
    public function getCount(StaticFilesMigrateConfig $config): int
    {
        $params = [
            'status' => $config->getStatus(),
        ];
        $extraCondition = '';
        if ($config->getExtension()) {
            $extraCondition = 'AND extension = :extension';
            $params['extension'] = $config->getExtension();
        }

        $res = $this->damMediaApiMigConnection->fetchOne(
            'SELECT count(id) FROM dam_document_mig WHERE migration_status = :status ' . $extraCondition,
            $params
        );

        if (is_numeric($res)) {
            return (int) $res;
        }

        return 0;
    }

    /**
     * @throws Exception
     */
    private function getMigrationItem(?int $fromId, StaticFilesMigrateConfig $config): Result
    {
        $params = [
            'status' => $config->getStatus(),
            'fromId' => $fromId ?? App::ZERO,
        ];
        $extraCondition = '';
        if ($config->getExtension()) {
            $extraCondition = ' AND extension = :extension';
            $params['extension'] = $config->getExtension();
        }

        return $this->damMediaApiMigConnection->executeQuery(
            '
            SELECT
                id,
                asset_file_id,
                author_uuid,
                author,
                file_name,
                file_path,
                cms_domain,
                file_size,
                cms_section,
                created_by,
                active,
                extension,
                migration_status,
                fail_reason,
                output_log
            FROM dam_document_mig
                WHERE id > :fromId and migration_status = :status ' . $extraCondition . '
            LIMIT 100
        ',
            $params
        );
    }
}
