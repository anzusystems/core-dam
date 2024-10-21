<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use App\App;
use App\Model\MediaApiMigration\ImageMigrationDto;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Exception;
use Generator;
use League\Flysystem\FilesystemException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;

#[AsCommand(
    name: 'anzu:media-api:generate-update-sql',
    description: 'Migrate MediaApi'
)]
final class MediaApiGenerateUpdateSqlCommand extends Command
{
    use OutputUtilTrait;

    public function __construct(
        private readonly Connection $damMediaApiMigConnection,
        private readonly Connection $defaultConnection,
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     * @throws FilesystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileName = App::getDataDir() . '/mediaUpdateSql.sql';
        $output->writeln('Writing file to ' . $fileName);
        file_put_contents($fileName, '');

        $progress = new ProgressBar($output);
        $progress->setFormat('debug');
        $progress->start();

        foreach ($this->iterate() as $row) {
            $licenceId = $this->findLicence($row->getMainFileId());
            if (false === $licenceId) {
                $output->writeln('Licence not found, skipping');

                continue;
            }

            file_put_contents(
                $fileName,
                sprintf(
                    'UPDATE mediaapi_image SET anzu_dam_uuid = %s, anzu_dam_licence_id = %d WHERE id_image = %d;' . PHP_EOL,
                    Uuid::fromRfc4122($row->getMainFileId())->toHex(),
                    $licenceId,
                    $row->getMediaApiId()
                ),
                FILE_APPEND
            );
            $progress->advance();
        }
        $progress->finish();
        $output->writeln('');

        return Command::SUCCESS;
    }

    /**
     * @return Generator<int, ImageMigrationDto>
     * @throws \Doctrine\DBAL\Exception
     */
    private function iterate(): Generator
    {
        $lastId = 0;
        do {
            $rows = $this->getMigrationItem($lastId)->fetchAllAssociative();

            foreach ($rows as $row) {
                $lastId = $row['media_api_id'];

                yield ImageMigrationDto::createdFromArray($row);
            }
        } while (100 === count($rows));
    }

    private function findLicence(string $assetFileId): int|false
    {
        return $this->defaultConnection->executeQuery(
            'SELECT licence_id FROM asset_file WHERE id = :id LIMIT 1',
            [
                'id' => $assetFileId,
            ]
        )->fetchOne();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function getMigrationItem(int $fromId): Result
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
            WHERE media_api_id > :fromId and migration_status = :status
            ORDER BY media_api_id ASC
            LIMIT 100
        ',
            [
                'fromId' => $fromId,
                'status' => 'migrated',
            ]
        );
    }
}
