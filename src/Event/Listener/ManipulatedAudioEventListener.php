<?php

declare(strict_types=1);

namespace App\Event\Listener;

use AnzuSystems\CoreDamBundle\Event\AssetFileDeleteEvent;
use AnzuSystems\CoreDamBundle\Event\ManipulatedAudioEvent;
use AnzuSystems\CoreDamBundle\Event\ManipulatedImageEvent;
use AnzuSystems\CoreDamBundle\Event\UserTrackingEvent;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use App\Messenger\Message\AudioCachePurgeMessage;
use App\Messenger\Message\ImageCachePurgeMessage;
use App\Security\Authenticator\Token\UgcImpAuthenticationToken;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ManipulatedAudioEvent::class)]
final class ManipulatedAudioEventListener
{
    use MessageBusAwareTrait;

    public function __invoke(ManipulatedAudioEvent $event): void
    {
        $this->messageBus->dispatch(new AudioCachePurgeMessage(
            audioId: $event->getAudioId(),
            path: $event->getPublicPath(),
            extSystemSlug: $event->getExtSystemSlug(),
        ));
    }
}
