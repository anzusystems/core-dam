<?php

declare(strict_types=1);

namespace App\Event\Listener;

use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Event\AssetFileDeleteEvent;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use App\Messenger\Message\AssetFileRouteMessage;
use App\Messenger\Message\ImageCachePurgeMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: AssetFileDeleteEvent::class)]
final class AssetFileDeleteListener
{
    use MessageBusAwareTrait;

    public function __construct(
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

        foreach ($event->getRoutePaths() as $path) {
            $this->messageBus->dispatch(
                new AssetFileRouteMessage(
                    assetFileId: $event->getDeleteId(),
                    fullUrl: $path
                )
            );
        }
    }
}
