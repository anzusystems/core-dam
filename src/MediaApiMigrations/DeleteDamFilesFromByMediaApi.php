<?php

declare(strict_types=1);

namespace App\MediaApiMigrations;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Domain\Image\Crop\CropCache;
use AnzuSystems\CoreDamBundle\FileSystem\AbstractFilesystem;
use AnzuSystems\CoreDamBundle\FileSystem\FileSystemProvider;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Statement;
use League\Flysystem\FilesystemException;
use RuntimeException;

final class DeleteDamFilesFromByMediaApi
{
    use OutputUtilTrait;

    public const int LIMIT = 1_000;

    private ?Statement $mediaSelectImageStatement = null;
    private ?Statement $mediaSelectImageByIdStatement = null;
    private readonly ConnectionDecorator $damMediaApiMigConnectionDecorator;

    /**
     * @var array<string, AbstractFilesystem>
     */
    private array $filesystemMap = [];
    private ?Statement $selectToDeleteStatement = null;

    public function __construct(
        private readonly KeywordProvider $keywordProvider,
        private readonly AuthorProvider $authorProvider,
        private readonly Connection $mediaApiConnection,
        private readonly Connection $defaultConnection,
        private readonly Connection $damMediaApiMigConnection,
        private readonly FileSystemProvider $fileSystemProvider,
        private readonly CropCache $cropCache,
    ) {
        $this->damMediaApiMigConnectionDecorator = new ConnectionDecorator($damMediaApiMigConnection);
    }

    /**
     * @throws Exception
     * @throws FilesystemException
     */
    public function migrate(): void
    {
        $progress = $this->outputUtil->createProgressBar((int) $this->damMediaApiMigConnection->executeQuery(
            'SELECT count(*) FROM dam_image_media_api_drop'
        )->fetchOne());
        $progress->setFormat('debug');
        $progress->start();

        $lastId = 0;
        do {
            $items = $this->selectToDelete($lastId);
            foreach ($items as $item) {
                $lastId = $item['id'];
                $this->deleteImageFile($item['main_file_id'], $item['asset_id'], $item['ext_slug']);
                $progress->advance();
            }
        } while (self::LIMIT === count($items));

        $progress->finish();
    }

    public function deleteImageFile(string $imageFileId, string $assetFileId, string $extSystemSlug): void
    {
        $storage = $this->getCmsImageStorage($extSystemSlug);

        $path = $this->defaultConnection->executeQuery('select asset_attributes_file_path from asset_file where id = :image_id', ['image_id' => $imageFileId])->fetchOne();
        $paths = $this->defaultConnection->executeQuery('select file_path from image_file_optimal_resize where image_id = :image_id', ['image_id' => $imageFileId])->fetchFirstColumn();

        $this->defaultConnection->beginTransaction();

        try {
            $this->defaultConnection->executeQuery('DELETE FROM asset_slot where image_id = :image_id', ['image_id' => $imageFileId]);
            $this->defaultConnection->executeQuery('DELETE FROM region_of_interest where image_id = :image_id', ['image_id' => $imageFileId]);
            $this->defaultConnection->executeQuery('DELETE FROM image_file_optimal_resize where image_id = :image_id', ['image_id' => $imageFileId]);
            $this->defaultConnection->executeQuery('DELETE FROM image_file where id = :image_id', ['image_id' => $imageFileId]);
            $this->defaultConnection->executeQuery('DELETE FROM asset_file where id = :image_id', ['image_id' => $imageFileId]);
            $this->defaultConnection->executeQuery('DELETE FROM asset where id = :asset_file', ['asset_file' => $assetFileId]);

            $this->defaultConnection->commit();
        } catch (\Throwable) {
            if ($this->defaultConnection->isTransactionActive()) {
                $this->defaultConnection->rollBack();
            }

            return;
        }

        // remove origin file
        if (is_string($path) && $storage->fileExists($path)) {
            $storage->delete($path);
        }

        // remove optimal resizes
        foreach ($paths as $path) {
            if ($storage->fileExists($path)) {
                $storage->delete($path);
            }
        }

        // remove crop cache
        $this->cropCache->removeCacheByOriginFilePath($extSystemSlug, $path);
    }

    private function getCmsImageStorage(string $slug): AbstractFilesystem
    {
        if (false === isset($this->filesystemMap[$slug])) {
            $storageName = $this->fileSystemProvider->getStorageNameBySlugAndType($slug, AssetType::Image);
            $fileSystem = $this->fileSystemProvider->getFileSystemByStorageName($storageName);

            if (null === $fileSystem) {
                throw new RuntimeException('Image ext system for slug not found. ' . $slug);
            }

            $this->filesystemMap[$slug] = $fileSystem;
        }

        return $this->filesystemMap[$slug];
    }

    private function selectToDelete(int $lastId): array
    {
        if (null === $this->selectToDeleteStatement) {
            $this->selectToDeleteStatement = $this->damMediaApiMigConnection->prepare(
                '
                SELECT id, media_api_id, main_file_id, asset_id, ext_slug FROM dam_image_media_api_drop
                WHERE id > :lastId
                ORDER BY id ASC 
                LIMIT ' . self::LIMIT
            );
        }

        $this->selectToDeleteStatement->bindValue(':lastId', $lastId);

        return $this->selectToDeleteStatement->executeQuery()->fetchAllAssociative();
    }
}
