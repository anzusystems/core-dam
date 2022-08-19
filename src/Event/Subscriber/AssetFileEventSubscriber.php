<?php

declare(strict_types=1);


namespace App\Event\Subscriber;

use Anzu\CommonBundle\AnzuSerializer\Exception\AnzuSerializerException;
use AnzuSystems\CoreDamBundle\Event\AssetFileChangeStateEvent;
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
     * @throws AnzuSerializerException
     */
    public function onAssetChangeState(AssetFileChangeStateEvent $event): void
    {
        $this->dispatcher->notifyAssetFileChanged($event);
    }
}