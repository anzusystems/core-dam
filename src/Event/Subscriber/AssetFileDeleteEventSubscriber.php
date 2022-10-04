<?php

declare(strict_types=1);

namespace App\Event\Subscriber;

use AnzuSystems\CoreDamBundle\Event\AssetFileDeleteEvent;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Notification\AssetFileNotificationDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AssetFileDeleteEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AssetFileNotificationDispatcher $dispatcher
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AssetFileDeleteEvent::class => 'deleteAssetFile',
        ];
    }

    /**
     * @throws SerializerException
     */
    public function deleteAssetFile(AssetFileDeleteEvent $event): void
    {
        $this->dispatcher->notifyAssetFileDeleted($event);
    }
}
