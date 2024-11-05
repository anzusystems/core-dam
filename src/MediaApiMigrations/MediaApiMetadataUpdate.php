<?php

declare(strict_types=1);

namespace App\MediaApiMigrations;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\FileSystem\AbstractFilesystem;
use AnzuSystems\CoreDamBundle\FileSystem\TmpLocalFilesystem;
use App\Domain\Image\MediaApi\LicenceProvider;
use App\Model\MediaApiMigrateConfig;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use League\Flysystem\FilesystemException;
use Symfony\Component\Uid\Uuid;

final class MediaApiMetadataUpdate
{
    use OutputUtilTrait;

    public const int LIMIT = 500;

    private AbstractFilesystem $sourceFileSystem;
    private TmpLocalFilesystem $tmpFileSystem;
    private ?Statement $selectAssetStatement = null;
    private ?Statement $selectKeywordsStatement = null;
    private ?Statement $selectAuthorsStatement = null;
    private ?Statement $insertAuthorStatement = null;
    private ?Statement $insertKeywordStatement = null;
    private ?Statement $updateCustomDataStatement = null;
    private ?Statement $updateSingleUseStatement = null;

    public function __construct(
        private readonly KeywordProvider $keywordProvider,
        private readonly AuthorProvider $authorProvider,
        private readonly Connection $mediaApiConnection,
        private readonly Connection $defaultConnection,
    ) {
    }

    /**
     * @throws Exception
     * @throws FilesystemException
     */
    public function migrate(int $fromId = null, int $toId = null): void
    {
        $progress = $this->outputUtil->createProgressBar($this->getMediaApiImagesCount($fromId, $toId));

        $progress->setFormat('debug');
        $progress->start();

        $lastId = $fromId;
        do {
            $rows = $this->getMediaApiImages($lastId, $toId)->fetchAllAssociative();
            foreach ($rows as $row) {
                $progress->advance();
                $lastId = $row['id_image'];

                $this->updateMetadata($row);
            }

            $this->authorProvider->clearCache();
            $this->keywordProvider->clearCache();
        } while (self::LIMIT === count($rows));

        $progress->finish();
    }

    private function updateMetadata(array $row): void
    {
        $assetRow = $this->selectAsset($row);
        if (null === $assetRow) {
            return;
        }

        $this->updateAssetFileSingleUse($row);
        $this->updateDescription($row, $assetRow);
        $this->updateKeywords($row, $assetRow);
        $this->updateAuthors($row, $assetRow);
    }

    private function updateDescription(array $row, array $assetRow): void
    {
        $metadata = json_decode($assetRow['custom_data'], true);
        $description = $metadata['description'] ?? '';

        if (empty($description) && false === empty($row['description']) && false === ($row['description'] === $description)) {
            $metadata['description'] = $row['description'];
            $this->updateCustomData($assetRow['metadata_id'], $metadata);
        }
    }

    private function updateAssetFileSingleUse(array $row): void
    {
        $uuid = Uuid::fromBinary($row['anzu_dam_uuid']);
        if (empty($this->updateSingleUseStatement)) {
            $this->updateSingleUseStatement = $this->defaultConnection->prepare(
                'UPDATE asset_file set flags_single_use = :flags_single_use WHERE id = :id'
            );
        }

        $this->updateSingleUseStatement->bindValue('flags_single_use', $row['single_use']);
        $this->updateSingleUseStatement->bindValue('id', $uuid->toRfc4122());
        $this->updateSingleUseStatement->executeStatement();
    }

    private function updateCustomData(string $metadataId, array $customData): void
    {
        if (empty($this->updateCustomDataStatement)) {
            $this->updateCustomDataStatement = $this->defaultConnection->prepare(
                'UPDATE asset_metadata set custom_data = :custom_data WHERE id = :metadata_id'
            );
        }

        $this->updateCustomDataStatement->bindValue('custom_data', json_encode($customData));
        $this->updateCustomDataStatement->bindValue('metadata_id', $metadataId);
        $this->updateCustomDataStatement->executeStatement();
    }

    private function updateKeywords(array $row, array $assetRow): void
    {
        /** @var array<int, string> $mediaApiKeywords */
        $mediaApiKeywords = array_unique(array_filter(
            array_map(
                fn (string $item): string => trim($item),
                explode(',', $row['keywords'] ?? '')
            ),
            fn (string $item): bool => false === empty($item)
        ));

        $keywords = $this->selectKeywords($assetRow);
        $missingKeywords = [];
        foreach ($mediaApiKeywords as $mediaApiKeyword) {
            $existingAuthor = array_filter($keywords, fn (array $keyword) => $keyword['name'] === $mediaApiKeyword);
            if (empty($existingAuthor)) {
                $missingKeywords[] = $mediaApiKeyword;
            }
        }

        if (empty($missingKeywords)) {
            return;
        }

        foreach ($missingKeywords as $missingKeyword) {
            $keywordId = $this->keywordProvider->getKeyword($missingKeyword, MigrationTableBuilder::CMS_EXT_ID);
            $existingKeyword = array_filter($keywords, fn (array $keyword) => $keyword['id'] === $keywordId);

            if (false === empty($existingKeyword)) {
                continue;
            }

            $this->insertKeyword($assetRow['asset_id'], $keywordId);
        }
    }

    private function insertKeyword(string $assetId, string $keywordId): void
    {
        if (null === $this->insertKeywordStatement) {
            $this->insertKeywordStatement = $this->defaultConnection->prepare(
                'INSERT INTO asset_keyword (asset_id, keyword_id) VALUES (:asset_id, :keyword_id) ON DUPLICATE KEY UPDATE asset_id = asset_id, keyword_id = keyword_id'
            );
        }

        $this->insertKeywordStatement->bindValue('asset_id', $assetId);
        $this->insertKeywordStatement->bindValue('keyword_id', $keywordId);

        $this->insertKeywordStatement->executeStatement();
    }

    private function updateAuthors(array $row, array $assetRow): void
    {
        $mediaApiAuthor = trim((string) $row['author']);
        if (empty($mediaApiAuthor)) {
            return;
        }

        $authors = $this->selectAuthors($assetRow);

        $existingAuthor = array_filter($authors, fn (array $author) => $author['name'] === $mediaApiAuthor);
        if (false === empty($existingAuthor)) {
            return;
        }

        $authorId = $this->authorProvider->getAuthor($mediaApiAuthor, MigrationTableBuilder::CMS_EXT_ID);
        $existingAuthor = array_filter($authors, fn (array $author) => $author['id'] === $authorId);

        if (false === empty($existingAuthor)) {
            return;
        }

        $this->insertAuthor($assetRow['asset_id'], $authorId);
    }

    private function selectAsset(array $row): ?array
    {
        $uuid = Uuid::fromBinary($row['anzu_dam_uuid']);
        if (null === $this->selectAssetStatement) {
            $this->selectAssetStatement = $this->defaultConnection->prepare(
                '
                select
                    i.asset_id,
                    am.custom_data,
                    am.id as metadata_id
                from image_file i
                inner join asset ass ON i.asset_id = ass.id
                inner join asset_metadata am ON ass.metadata_id = am.id
                where i.id = :image_id'
            );
        }
        $this->selectAssetStatement->bindValue('image_id', $uuid->toRfc4122());

        return $this->selectAssetStatement->executeQuery()->fetchAllAssociative()[0] ?? null;
    }

    private function selectKeywords(array $assetRow): array
    {
        if (null === $this->selectKeywordsStatement) {
            $this->selectKeywordsStatement = $this->defaultConnection->prepare(
                '
                select k.id, k.name from keyword k
                INNER JOIN asset_keyword ak ON k.id = ak.keyword_id
                where ak.asset_id = :asset_id'
            );
        }

        $this->selectKeywordsStatement->bindValue('asset_id', $assetRow['asset_id']);

        return $this->selectKeywordsStatement->executeQuery()->fetchAllAssociative();
    }

    private function selectAuthors(array $assetRow): array
    {
        if (null === $this->selectAuthorsStatement) {
            $this->selectAuthorsStatement = $this->defaultConnection->prepare(
                '
                select a.id, a.name from author a
                INNER JOIN asset_author aa ON a.id = aa.author_id
                where aa.asset_id = :asset_id'
            );
        }

        $this->selectAuthorsStatement->bindValue('asset_id', $assetRow['asset_id']);

        return $this->selectAuthorsStatement->executeQuery()->fetchAllAssociative();
    }

    private function insertAuthor(string $assetId, string $authorId): void
    {
        if (null === $this->insertAuthorStatement) {
            $this->insertAuthorStatement = $this->defaultConnection->prepare(
                'INSERT INTO asset_author (asset_id, author_id) VALUES (:asset_id, :author_id) ON DUPLICATE KEY UPDATE asset_id = asset_id, author_id = author_id'
            );
        }

        $this->insertAuthorStatement->bindValue('asset_id', $assetId);
        $this->insertAuthorStatement->bindValue('author_id', $authorId);

        $this->insertAuthorStatement->executeStatement();
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
                    img.description,
                    img.author,
                    img.keywords,
                    img.source_url,
                    img.from_migration,
                    img.anzu_dam_uuid,
                    if (img.id_licence = 3, 1, 0) single_use
                FROM mediaapi_image img
                INNER JOIN mediaapi_stock stock ON img.id_stock = stock.id_stock
                WHERE img.id_image > :fromId and img.id_image <= :toId 
                AND anzu_dam_uuid is not null
                AND img.id_stock in (:stocks)
                LIMIT ' . self::LIMIT . '
            ',
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
            'SELECT count(id_image) FROM mediaapi_image WHERE id_image > :fromId AND id_image <= :toId AND anzu_dam_uuid is not null AND id_stock in (:stocks)',
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
