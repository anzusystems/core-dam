<?php

declare(strict_types=1);

namespace App\Event\Subscriber;

use AnzuSystems\CoreDamBundle\Domain\Asset\AssetTextsProcessor;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Event\AssetFileChangeStateEvent;
use AnzuSystems\CoreDamBundle\Exception\RuntimeException;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\CoreDamBundle\Repository\AudioFileRepository;
use AnzuSystems\CoreDamBundle\Repository\ImageFileRepository;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Domain\AssetMetadata\AssetMetadataManager;
use App\Domain\Image\MediaApi\ImageFacade;
use App\Domain\Image\MediaApi\ImageManager;
use App\Model\Domain\AssetMetadata\MediaApiMetadata;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class MediaApiChangeStateEvent implements EventSubscriberInterface
{
    use MessageBusAwareTrait;

    public function __construct(
        private readonly AudioFileRepository $audioFileRepository,
        private readonly ImageManager $imageManager,
        private readonly AssetMetadataManager $assetMetadataManager,
        private readonly ImageFileRepository $imageFileRepository,
        private readonly ImageFacade $imageFacade
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AssetFileChangeStateEvent::class => 'onAssetChangeState',
        ];
    }

    /**
     * @throws SerializerException
     */
    public function onAssetChangeState(AssetFileChangeStateEvent $event): void
    {
        $imageFile = $event->getAsset();
        if (false === ($imageFile instanceof ImageFile)) {
            return;
        }

        $mediaApiMetadata = $this->assetMetadataManager->getObjectFromMetadata(
            $imageFile->getAsset()->getMetadata(),
            MediaApiMetadata::class
        );

        if (empty($mediaApiMetadata->getMediaApiIds())) {
            return;
        }

        if ($imageFile->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Processed)) {
            $this->imageFacade->updateAfterProcessed($imageFile);

            return;
        }

        $originAssetFile = $this->imageFileRepository->find(
            $imageFile->getAssetAttributes()->getOriginAssetId()
        );

        if ($originAssetFile && $imageFile->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Duplicate)) {
            $this->imageFacade->updateFromDuplicate($originAssetFile, $imageFile);
        }
    }
}
