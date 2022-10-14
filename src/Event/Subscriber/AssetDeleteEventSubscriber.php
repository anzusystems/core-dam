<?php

declare(strict_types=1);

namespace App\Event\Subscriber;

use AnzuSystems\CoreDamBundle\Event\AssetDeleteEvent;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Notification\AssetNotificationDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AssetDeleteEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AssetNotificationDispatcher $notificationDispatcher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AssetDeleteEvent::class => 'deleteAssetFile',
        ];
    }

    /**
     * @throws SerializerException
     */
    public function deleteAssetFile(AssetDeleteEvent $event): void
    {
        $this->notificationDispatcher->notifyAssetDeleted($event);
    }
}
