<?php

declare(strict_types=1);

namespace App\Event\Subscriber;

use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Event\AssetFileChangeStateEvent;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\CoreDamBundle\Repository\AudioFileRepository;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Domain\ArtemisAudioDistribution\ArtemisAudioDistributionAutomat;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AssetFileEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ArtemisAudioDistributionAutomat $automat,
        private readonly AudioFileRepository $audioFileRepository,
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
     * @throws NonUniqueResultException
     */
    public function onAssetChangeState(AssetFileChangeStateEvent $event): void
    {
        $assetFile = $event->getAsset();
        if (
            $assetFile->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Processed) &&
            $assetFile instanceof AudioFile
        ) {
            $this->automat->makeAudioPublicUrl($assetFile);
            $this->automat->tryToDistribute($assetFile);
        }

        if (
            $assetFile->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Duplicate) &&
            $assetFile instanceof AudioFile &&
            false === empty($assetFile->getAssetAttributes()->getOriginAssetId())
        ) {
            $originAudioFile = $this->audioFileRepository->find($assetFile->getAssetAttributes()->getOriginAssetId());
            if (null === $originAudioFile) {
                return;
            }

            $this->automat->makeAudioPublicUrl($originAudioFile);
            $this->automat->tryToDistribute($originAudioFile);
        }
    }
}
