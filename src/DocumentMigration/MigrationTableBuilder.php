<?php

declare(strict_types=1);

namespace App\DocumentMigration;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Exception\DomainException;
use AnzuSystems\CoreDamBundle\FileSystem\AbstractFilesystem;
use AnzuSystems\CoreDamBundle\FileSystem\FileSystemProvider;
use App\App;
use App\Csv\CsvFactory;
use App\MediaApiMigrations\ConnectionDecorator;
use App\Model\Csv\DocumentCsvFile;
use App\Model\StaticFilesMigrateConfig;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\String\ByteString;

final class MigrationTableBuilder
{
    use OutputUtilTrait;
    public const ALLOWED_EXTENSION_LIST = [
        'pdf', 'jpg', 'jpeg', 'png', 'gif', 'xlsx', 'doc', 'ppt', 'gif', 'mp3', 'html', 'xls', 'docx', 'svg', 'mp4',
    ];

    private const BULK_SIZE = 15;

    private readonly ConnectionDecorator $damMediaApiMigConnectionDecorator;
    private AbstractFilesystem $docsFilesystem;

    public function __construct(
        private readonly FileSystemProvider $fileSystemProvider,
        private readonly Connection $damMediaApiMigConnection,
        private readonly CsvFactory $csvFactory,
    ) {
        $this->damMediaApiMigConnectionDecorator = new ConnectionDecorator($damMediaApiMigConnection);
    }

    /**
     * @throws Exception
     */
    public function buildTable(StaticFilesMigrateConfig $config): void
    {
        $this->dropTable();
        $this->createTable();
        $this->populateTable($config);
    }

    private function populateTable(StaticFilesMigrateConfig $config): void
    {
        $sourceFileSystem = $this->fileSystemProvider->getFileSystemByStorageName('cms.document');
        if (null === $sourceFileSystem) {
            throw new DomainException('Filesystem not exists');
        }
        $file = $this->fileSystemProvider->getTmpFileSystem()->writeTmpFileFromStream(
            $sourceFileSystem->readStream($config->getFileName())
        );

        $csvFile = $this->csvFactory->initCsv((string) $file->getRealPath(), DocumentCsvFile::class, true);

        $progres = $this->outputUtil->createProgressBar();
        $progres->start();

        $docsFilesystem = $this->fileSystemProvider->getFileSystemByStorageName('general.documents');
        if (null === $docsFilesystem) {
            throw new DomainException('Filesystem not exists');
        }
        $this->docsFilesystem = $docsFilesystem;

        $i = 0;
        /** @var DocumentCsvFile $value */
        foreach ($csvFile->readObject() as $value) {
            if (
                $value->getActive() > App::ZERO &&
                false === in_array($value->getExtension(), self::ALLOWED_EXTENSION_LIST, true)
            ) {
                $this->outputUtil->writeln('');
                $this->outputUtil->writeln('Skipp: https://artemis.smedata.sk/usmedata' . $value->getFilePath());
                continue;
            }

            if ($value->getActive() > App::ZERO) {
                $this->insertToDelta($value);
                $i++;
                if (App::ZERO === $i % self::BULK_SIZE) {
                    $this->damMediaApiMigConnectionDecorator->flush();
                }
            }
            $progres->advance();
        }

        $this->damMediaApiMigConnectionDecorator->flush();
        $progres->finish();
        $this->outputUtil->writeln('');
    }

    private function dropTable(): void
    {
        $this->damMediaApiMigConnection->executeQuery('DROP TABLE IF EXISTS dam_document_mig');
        $this->outputUtil->info('Table dropped');
    }

    /**
     * @throws Exception
     */
    private function createTable(): void
    {
        $sql = '
        CREATE TABLE IF NOT EXISTS dam_document_mig(
            id int unsigned auto_increment primary key,
            asset_file_id char(36) not null default \'\',
            author_uuid char(36) not null default \'\',
            author varchar(255) not null default \'\',
            file_name varchar(255) not null default \'\',
            file_path varchar(255) not null default \'\',
            cms_domain varchar(255) not null default \'\',
            file_size int not null default 0,
            cms_section int not null default 0,
            created_by int not null default 0,
            created_at datetime default null,
            active smallint not null default 0,
            extension char(16) not null default \'\',
            migration_status varchar(255) not null default \'waiting\',
            fail_reason varchar(255) not null default \'\',
            output_log varchar(255) not null default \'\'
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;';

        $this->damMediaApiMigConnection->executeQuery($sql);

        $this->damMediaApiMigConnection->executeQuery(
            'create index IDX_ASSET_ID on dam_document_mig(asset_file_id);'
        );

        $this->outputUtil->info('Table created');
    }

    private function insertToDelta(DocumentCsvFile $file): void
    {
        $fileName = (new ByteString($file->getFileName()))->toUnicodeString();
        $authorName = (new ByteString($file->getAuthor()))->toUnicodeString();

        $this->damMediaApiMigConnectionDecorator->prepareBulkInsert(
            'dam_document_mig',
            [
                'id' => $file->getId(),
                'asset_file_id' => '',
                'author_uuid' => '',
                'author' => $authorName->toString(),
                'file_name' => $fileName->toString(),
                'file_path' => $file->getFilePath(),
                'file_size' => $file->getFileSize(),
                'active' => $file->getActive(),
                'created_by' => $file->getCreatedById(),
                'extension' => strtolower($file->getExtension()),
                'cms_domain' => $file->getCmsDomain(),
                'cms_section' => $file->getCmsSection(),
                'created_at' => $this->getCreatedAt($file)?->format(DateTimeImmutable::ATOM),
            ],
        );
    }

    private function getCreatedAt(DocumentCsvFile $file): ?DateTimeImmutable
    {
        if ('NULL' === $file->getCreatedAt() || empty($file->getCreatedAt())) {
            return null;
        }

        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $file->getCreatedAt());

        if (false === $dateTime) {
            return null;
        }

        if ($dateTime < App::getMinDate()) {
            return null;
        }

        return $dateTime;
    }
}
