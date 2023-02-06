<?php

declare(strict_types=1);

namespace App\Event\Subscriber;

use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use AnzuSystems\CoreDamBundle\Event\AssetFileChangeStateEvent;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Domain\ArtemisAudioDistribution\ArtemisAudioDistributionAutomat;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AssetFileEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ArtemisAudioDistributionAutomat $automat,
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
        $assetFile = $event->getAsset();
        if (
            $assetFile->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Processed) &&
            $assetFile instanceof AudioFile
        ) {
            $this->automat->tryToDistribute($assetFile);
        }
    }
}
