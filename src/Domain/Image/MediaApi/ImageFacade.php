<?php

declare(strict_types=1);

namespace App\Domain\Image\MediaApi;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Validator\Validator;
use AnzuSystems\CoreDamBundle\Domain\Asset\AssetTextsProcessor;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileManagerProvider;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileMessageDispatcher;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Entity\Author;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Entity\Keyword;
use AnzuSystems\CoreDamBundle\Exception\DomainException;
use AnzuSystems\CoreDamBundle\Exception\RuntimeException;
use AnzuSystems\CoreDamBundle\Logger\DamLogger;
use AnzuSystems\CoreDamBundle\Traits\IndexManagerAwareTrait;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Domain\Asset\AssetRtmpFactory;
use App\Messenger\Message\MediaApiCallbackMessage;
use App\Model\Domain\Asset\AssetFileMediaApiCreateDecorator;
use App\Model\Domain\Asset\AssetFileMediaApiDecorator;
use App\Model\Domain\Asset\AssetFileMediaApiResponseDecorator;
use App\Model\Dto\Asset\RtmpAssetDto;
use Doctrine\ORM\NonUniqueResultException;
use League\Flysystem\FilesystemException;
use Throwable;

final class ImageFacade
{
    use IndexManagerAwareTrait;
    use MessageBusAwareTrait;

    private const MEDIA_API_NAMESPACE = 'MediaApi sync';

    public function __construct(
        private readonly Validator $validator,
        private readonly ImageFactory $factory,
        private readonly AssetFileManagerProvider $assetFileManagerProvider,
        private readonly AssetFileMessageDispatcher $assetFileMessageDispatcher,
        private readonly ImageManager $imageManager,
        private readonly AssetTextsProcessor $assetTextsProcessor,
        private readonly DamLogger $damLogger,
    ) {
    }

    /**
     * @throws FilesystemException
     * @throws NonUniqueResultException
     * @throws ValidationException
     */
    public function create(AssetFileMediaApiCreateDecorator $dto): AssetFile
    {
        $this->validator->validate($dto);
        $assetFile = $this->factory->createFromMediaApi($dto);

        try {
            $this->imageManager->beginTransaction();
            $this->imageManager->flush();
            $this->indexRelations($assetFile);
            $this->imageManager->commit();
        } catch (Throwable $exception) {
            $this->imageManager->rollback();

            throw new RuntimeException('asset_file_create_failed', 0, $exception);
        }

        $this->assetFileMessageDispatcher->dispatchAssetFileChangeState($assetFile);

        return $assetFile;
    }

    /**
     * @throws ValidationException
     */
    public function update(ImageFile $image, AssetFileMediaApiDecorator $dto): AssetFile
    {
        $this->validator->validate($dto);

        try {
            $this->imageManager->beginTransaction();
            $this->imageManager->updateFromDto($image, $dto);
            $this->indexRelations($image);
            $this->imageManager->commit();
        } catch (Throwable $exception) {
            $this->imageManager->rollback();

            throw new RuntimeException('asset_file_update_failed', 0, $exception);
        }

        return $image;
    }

    /**
     * @throws SerializerException
     */
    public function updateAfterProcessed(ImageFile $image): AssetFile
    {
        try {
            $this->imageManager->beginTransaction();
            $this->imageManager->updateFromExif($image, false);
            $this->assetTextsProcessor->updateAssetDisplayTitle($image->getAsset());
            $this->imageManager->flush();
            $this->indexRelations($image);
            $this->imageManager->commit();
        } catch (Throwable $exception) {
            $this->imageManager->rollback();

            $this->damLogger->error(
                self::MEDIA_API_NAMESPACE,
                sprintf(
                    'Update after processed failed with message (%s) for imageId (%s)',
                    $exception->getMessage(),
                    (string) $image->getId()
                )
            );
        }

        return $image;
    }

    /**
     * @throws SerializerException
     */
    public function updateFromDuplicate(ImageFile $originImageFile, ImageFile $imageFile): AssetFile
    {
        try {
            $this->imageManager->beginTransaction();
            $this->imageManager->updateFromDuplicate($originImageFile, $imageFile);
            $this->messageBus->dispatch(new MediaApiCallbackMessage($imageFile));
            $this->indexRelations($originImageFile);
            $this->imageManager->commit();
        } catch (Throwable $exception) {
            $this->imageManager->rollback();

            $this->damLogger->error(
                self::MEDIA_API_NAMESPACE,
                sprintf(
                    'Update from duplicate failed with message (%s) for imageId (%s)',
                    $exception->getMessage(),
                    (string) $originImageFile->getId()
                )
            );
        }

        return $originImageFile;
    }

    private function indexRelations(AssetFile $assetFile): void
    {
        foreach ($assetFile->getAsset()->getAuthors() as $author) {
            $this->indexManager->index($author);
        }

        foreach ($assetFile->getAsset()->getKeywords() as $keyword) {
            $this->indexManager->index($keyword);
        }
    }
}
