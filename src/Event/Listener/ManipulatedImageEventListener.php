<?php

declare(strict_types=1);

namespace App\Event\Listener;

use AnzuSystems\CoreDamBundle\Event\AssetFileDeleteEvent;
use AnzuSystems\CoreDamBundle\Event\ManipulatedImageEvent;
use AnzuSystems\CoreDamBundle\Event\UserTrackingEvent;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use App\Messenger\Message\ImageCachePurgeMessage;
use App\Security\Authenticator\Token\UgcImpAuthenticationToken;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ManipulatedImageEvent::class)]
final class ManipulatedImageEventListener
{
    use MessageBusAwareTrait;

    public function __invoke(ManipulatedImageEvent $event): void
    {
        $this->messageBus->dispatch(new ImageCachePurgeMessage(
            imageId: $event->getImageId(),
            roiPositions: $event->getRoiPositions(),
            extSystemSlug: $event->getExtSystem()
        ));
    }
}
