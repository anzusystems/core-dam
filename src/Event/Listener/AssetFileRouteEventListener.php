<?php

declare(strict_types=1);

namespace App\Event\Listener;

use AnzuSystems\CoreDamBundle\Event\AssetFileRouteEvent;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use App\Messenger\Message\AssetFileRouteMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: AssetFileRouteEvent::class)]
final class AssetFileRouteEventListener
{
    use MessageBusAwareTrait;

    public function __invoke(AssetFileRouteEvent $event): void
    {
        $this->messageBus->dispatch(new AssetFileRouteMessage(
            assetFileId: $event->getAssetFileId(),
            fullUrl: $event->getFullUrl()
        ));
    }
}
