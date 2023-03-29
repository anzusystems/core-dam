<?php

declare(strict_types=1);

namespace App\MediaApiMigrations;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\FileSystem\FileSystemProvider;
use App\Model\MediaApiMigrateConfig;
use Doctrine\DBAL\Exception;
use League\Flysystem\FilesystemException;

final class MediaApiFileCopy
{
    use OutputUtilTrait;

    public function __construct(
        private readonly MigrationTableIterator $migrationIterator,
        private readonly FileSystemProvider $fileSystemProvider,
    ) {
    }

    /**
     * @throws Exception
     * @throws FilesystemException
     */
    public function migrate(MediaApiMigrateConfig $config): void
    {
        $sourceFileSystem = $this->fileSystemProvider->getFileSystemByStorageName(MediaApiMigrateConfig::REMOTE_STORAGE);
        if (null === $sourceFileSystem) {
            return;
        }
        $targetFileSystem = $this->fileSystemProvider->getFileSystemByStorageName(MediaApiMigrateConfig::LOCAL_STORAGE);
        if (null === $targetFileSystem) {
            return;
        }

        $progress = $this->outputUtil->createProgressBar(
            $this->migrationIterator->getCount($config)
        );
        $progress->start();

        foreach ($this->migrationIterator->iterate($config) as $dto) {
            if ($sourceFileSystem->fileExists($dto->getFilePath()) && false === $targetFileSystem->fileExists($dto->getFilePath())) {
                $targetFileSystem->writeStream(
                    $dto->getFilePath(),
                    $sourceFileSystem->readStream($dto->getFilePath())
                );
            }

            $progress->advance();
        }

        $progress->finish();
        $this->outputUtil->writeln('');
    }
}
