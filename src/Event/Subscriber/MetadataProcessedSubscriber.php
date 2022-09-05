<?php

declare(strict_types=1);


namespace App\Event\Subscriber;

use AnzuSystems\CoreDamBundle\Event\MetadataProcessedEvent;
use App\Notification\AssetFileNotificationDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class MetadataProcessedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AssetFileNotificationDispatcher $dispatcher
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MetadataProcessedEvent::class => 'onAssetChangeState'
        ];
    }

    public function onAssetChangeState(MetadataProcessedEvent $event): void
    {
        $this->dispatcher->notifyMetadataProcessed($event);
    }
}