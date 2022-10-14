<?php

declare(strict_types=1);

namespace App\Event\Subscriber;

use AnzuSystems\CoreDamBundle\Event\AssetFileChangeStateEvent;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Notification\AssetFileNotificationDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AssetFileEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AssetFileNotificationDispatcher $dispatcher
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AssetFileChangeStateEvent::class => 'onAssetChangeState'
        ];
    }

    /**
     * @throws SerializerException
     */
    public function onAssetChangeState(AssetFileChangeStateEvent $event): void
    {
        $this->dispatcher->notifyAssetFileChanged($event);
    }
}
