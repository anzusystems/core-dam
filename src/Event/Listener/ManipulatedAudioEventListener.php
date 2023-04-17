<?php

declare(strict_types=1);

namespace App\Event\Listener;

use AnzuSystems\CoreDamBundle\Event\ManipulatedAudioEvent;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use App\Messenger\Message\AudioCachePurgeMessage;
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
