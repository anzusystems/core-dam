<?php

declare(strict_types=1);

namespace App\Event\Listener;

use AnzuSystems\CoreDamBundle\Cache\CachePurgeManager;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Event\AssetFileDeleteEvent;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use App\HttpClient\NotificationClient;
use App\Messenger\Message\AudioCachePurgeMessage;
use App\Messenger\Message\ImageCachePurgeMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: AssetFileDeleteEvent::class)]
final class AssetFileDeleteListener
{
    use MessageBusAwareTrait;

    public function __construct(
        private readonly NotificationClient $notificationClient,
    ) {
    }

    public function __invoke(AssetFileDeleteEvent $event): void
    {
        $assetFile = $event->getAssetFile();

        if ($assetFile instanceof ImageFile) {
            $this->messageBus->dispatch(new ImageCachePurgeMessage(
                imageId: $event->getDeleteId(),
                roiPositions: $event->getRoiPositions(),
                extSystemSlug: $event->getExtSystem()
            ));
        }

        // todo break into separate events
        if ($assetFile instanceof AudioFile && $assetFile->getAudioPublicLink()->isPublic()) {
            // todo remove public link from bucket

            $this->messageBus->dispatch(new AudioCachePurgeMessage(
                audioId: $event->getDeleteId(),
                path: $assetFile->getAudioPublicLink()->getPath(),
                extSystemSlug: $event->getExtSystem()
            ));
        }
    }
}
