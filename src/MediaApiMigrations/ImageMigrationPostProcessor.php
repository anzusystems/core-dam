<?php

declare(strict_types=1);

namespace App\MediaApiMigrations;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageManager;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Repository\ImageFileRepository;
use App\Model\MediaApiMigrateConfig;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final class ImageMigrationPostProcessor
{
    use OutputUtilTrait;

    private const int BULK_COUNT = 50;

    public function __construct(
        private readonly ImageFileRepository $imageFileRepository,
        private readonly ImageManager $imageManager,
        private readonly Connection $damMediaApiMigConnection,
    ) {
    }

    /**
     * @throws Exception
     */
    public function postProcess(MediaApiMigrateConfig $config): void
    {
        if (false === $config->allowImagePostprocess()) {
            return;
        }

        $progress = $this->outputUtil->createProgressBar();
        $progress->setFormat('debug');
        $progress->start();

        $assetFiles = $this->imageFileRepository->findAllProcessed(self::BULK_COUNT);
        $lastId = null;
        while (false === $assetFiles->isEmpty()) {
            /** @var ImageFile $assetFile */
            foreach ($assetFiles as $assetFile) {
                $lastId = $assetFile->getId();

                $this->updateCustomData($assetFile);

                $progress->advance();
            }

            $this->flushAndClear();
            $assetFiles = $this->imageFileRepository->findAllProcessed(self::BULK_COUNT, $lastId);
        }

        $this->flushAndClear();
    }

    /**
     * @throws Exception
     */
    private function updateCustomData(ImageFile $image): void
    {
        $res = $this->damMediaApiMigConnection->executeQuery(
            'SELECT media_api_id, file_path FROM dam_media_api_mig where main_file_id = :assetFileId',
            [
                'assetFileId' => (string) $image->getId(),
            ]
        )->fetchAllAssociative();

        if (empty($res)) {
            return;
        }

        $mediaApiIds = [];
        $mediaApiPaths = [];
        foreach ($res as $item) {
            if (isset($item['media_api_id'])) {
                $mediaApiIds[] = $item['media_api_id'];
            }
            if (isset($item['file_path'])) {
                $mediaApiPaths[] = $item['file_path'];
            }
        }

        $customData = $image->getAsset()->getMetadata()->getCustomData();
        $customData['mediaApiIds'] = $mediaApiIds;
        $customData['mediaApiPaths'] = $mediaApiPaths;
        $image->getAsset()->getMetadata()->setCustomData($customData);
    }

    private function flushAndClear(): void
    {
        $this->imageManager->flush();
        $this->imageManager->clear();
    }
}
