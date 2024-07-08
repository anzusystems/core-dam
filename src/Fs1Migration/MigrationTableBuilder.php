<?php

declare(strict_types=1);

namespace App\Fs1Migration;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Exception\DomainException;
use AnzuSystems\CoreDamBundle\FileSystem\FileSystemProvider;
use AnzuSystems\CoreDamBundle\Helper\FileNameHelper;
use App\App;
use App\Csv\CsvFactory;
use App\MediaApiMigrations\AuthorProvider;
use App\MediaApiMigrations\ConnectionDecorator;
use App\MediaApiMigrations\KeywordProvider;
use App\MediaApiMigrations\UserProvider;
use App\Model\Csv\Fs1CsvFile;
use App\Model\Fs1ApiMigrateConfig;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\String\ByteString;

final class MigrationTableBuilder
{
    use OutputUtilTrait;
    public const int CMS_EXT_ID = 1;
    private const int BULK_SIZE = 100;
    private const array IGNORE_RUBRIC_IDS = [8, 98];

    private readonly ConnectionDecorator $damMediaApiMigConnectionDecorator;

    public function __construct(
        private readonly Connection $damMediaApiMigConnection,
        private readonly CsvFactory $csvFactory,
        private readonly KeywordProvider $keywordProvider,
        private readonly AuthorProvider $authorProvider,
        private readonly UserProvider $userProvider,
        private readonly VideoShowProvider $videoShowProvider,
        private readonly CategoryProvider $categoryProvider,
        private readonly FileSystemProvider $fileSystemProvider,
    ) {
        $this->damMediaApiMigConnectionDecorator = new ConnectionDecorator($damMediaApiMigConnection);
    }

    /**
     * @throws Exception
     */
    public function buildTable(Fs1ApiMigrateConfig $config): void
    {
        $this->dropTable();
        $this->createTable();
        $this->populateTable($config);
    }

    private function dropTable(): void
    {
        $this->damMediaApiMigConnection->executeQuery('DROP TABLE IF EXISTS dam_fs1_mig');
        $this->outputUtil->info('Table dropped');
    }

    private function populateTable(Fs1ApiMigrateConfig $config): void
    {
        $sourceFileSystem = $this->fileSystemProvider->getFileSystemByStorageName($config->getStorageName());
        if (null === $sourceFileSystem) {
            throw new DomainException('Filesystem not exists');
        }
        $file = $this->fileSystemProvider->getTmpFileSystem()->writeTmpFileFromStream(
            $sourceFileSystem->readStream($config->getFileName())
        );

        $csvFile = $this->csvFactory->initCsv((string) $file->getRealPath(), Fs1CsvFile::class, true);

        $progres = $this->outputUtil->createProgressBar();
        $progres->start();

        $i = 0;
        /** @var Fs1CsvFile $value */
        foreach ($csvFile->readObject() as $value) {
            if (
                false === in_array($value->getRubricId(), self::IGNORE_RUBRIC_IDS, true) &&
                $value->getAvailable() > App::ZERO
            ) {
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

    /**
     * @throws Exception
     */
    private function createTable(): void
    {
        $sql = '
        CREATE TABLE IF NOT EXISTS dam_fs1_mig(
            media_id int unsigned auto_increment primary key,
            asset_id char(36) not null default \'\',
            title varchar(500) not null default \'\',
            pub_date datetime not null default now(),
            created_at datetime not null default now(),
            modified_at datetime not null default now(),
            rubric_id int unsigned not null default 0,
            created_by_id int unsigned not null default 0,
            rubric_title varchar(255) not null default \'\',
            show_id int unsigned not null default 0,
            show_title varchar(255) not null default \'\',
            authors JSON NOT NULL DEFAULT (JSON_OBJECT()),
            keywords JSON NOT NULL DEFAULT (JSON_OBJECT()),
            author_ids JSON NOT NULL DEFAULT (JSON_OBJECT()),
            keyword_ids JSON NOT NULL DEFAULT (JSON_OBJECT()),
            show_uuid char(36) not null default \'\',
            episode_uuid char(36) not null default \'\',
            category_uuid char(36) not null default \'\',
            yt_id varchar(255) not null default \'\',
            jw_id varchar(255) not null default \'\',
            media_url varchar(255) not null default \'\',
            article_url varchar(255) not null default \'\',
            dir varchar(255) not null default \'\',
            file_path varchar(255) not null default \'\',
            file_path_hd varchar(255) not null default \'\',
            file_path_image varchar(255) not null default \'\',
            migration_status varchar(255) not null default \'waiting\',
            fail_reason varchar(255) not null default \'\',
            output_log varchar(255) not null default \'\'
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;';

        $this->damMediaApiMigConnection->executeQuery($sql);

        $this->damMediaApiMigConnection->executeQuery(
            'create index IDX_ASSET_ID on dam_fs1_mig(asset_id);'
        );

        $this->outputUtil->info('Table created');
    }

    private function insertToDelta(Fs1CsvFile $file): void
    {
        $title = (new ByteString($file->getTitle()))->toUnicodeString();
        $rubricTitle = (new ByteString('NULL' === $file->getRubricName() ? '' : $file->getRubricName()))->toUnicodeString();
        $showTitle = (new ByteString('NULL' === $file->getSeriesName() ? '' : $file->getSeriesName()))->toUnicodeString();
        $cleanArray = fn (string $string): string => (new ByteString($string))->toUnicodeString()->trim()->toString();
        $cleanYoutubeCode = fn (string $string): string => 'NULL' === $string ? '' : explode('&', $string)[0] ?? '';

        $authors = array_map($cleanArray, $file->getAuthor());
        $keywords = array_map($cleanArray, $file->getAuthor());

        $this->damMediaApiMigConnectionDecorator->prepareBulkInsert(
            'dam_fs1_mig',
            [
                'media_id' => $file->getId(),
                'asset_id' => '',
                'title' => $title->toString(),
                'pub_date' => $file->getPublishedAt()->format(DateTimeInterface::ATOM),
                'created_at' => $file->getCreatedAt()->format(DateTimeInterface::ATOM),
                'modified_at' => $file->getModifiedAt()->format(DateTimeInterface::ATOM),
                'show_uuid' => $this->videoShowProvider->getShow($file),
                'category_uuid' => $this->categoryProvider->getCategory($file),
                'episode_uuid' => $this->videoShowProvider->getEpisode($file, $title->toString()),
                'created_by_id' => $file->getCreatedBy() > App::ZERO ? $this->userProvider->getUser($file->getCreatedBy()) : App::ZERO,
                'rubric_title' => $rubricTitle->toString(),
                'rubric_id' => $file->getRubricId(),
                'show_title' => $showTitle->toString(),
                'show_id' => $file->getSeriesId(),
                'authors' => json_encode($authors),
                'keywords' => json_encode($keywords),
                'keyword_ids' => json_encode(
                    array_map(
                        fn (string $keyword): string => $this->keywordProvider->getKeyword(
                            $keyword,
                            self::CMS_EXT_ID
                        ),
                        $keywords
                    )
                ),
                'author_ids' => json_encode(
                    array_map(
                        fn (string $author): string => $this->authorProvider->getAuthor(
                            $author,
                            self::CMS_EXT_ID
                        ),
                        $authors
                    )
                ),
                'yt_id' => $cleanYoutubeCode($file->getYoutubeCode()),
                'media_url' => 'NULL' === $file->getCmsAdminUrl() ? '' : $file->getCmsAdminUrl(),
                'article_url' => 'NULL' === $file->getCmsArticleUrl() ? '' : $file->getCmsArticleUrl(),
                'dir' => $file->getFilePath(),
                'file_path' => FileNameHelper::concatDirFile($file->getFilePath(), $file->getFileName()),
                'file_path_hd' => FileNameHelper::concatDirFile($file->getFilePath(), $file->getFileNameHdQuality()),
                'file_path_image' => FileNameHelper::concatDirFile($file->getFilePath(), $file->getImageFileName()),
            ],
            ['media_id = new_row.media_id']
        );
    }
}
