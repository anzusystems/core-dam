<?php

declare(strict_types=1);


namespace App\Notification;

use AnzuSystems\CommonBundle\Traits\SerializerAwareTrait;
use AnzuSystems\CoreDamBundle\Event\AssetDeleteEvent;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Model\Domain\AssetFile\AssetAdmNotificationDecorator;

final class AssetNotificationDispatcher extends AbstractNotificationDispatcher
{
    private const EVENT_ASSET_DELETED_NAME = 'asset_deleted';

    use SerializerAwareTrait;

    /**
     * @throws SerializerException
     */
    public function notifyAssetDeleted(AssetDeleteEvent $event): void
    {
        $this->notify(
            [$event->getDeletedBy()->getId()],
            self::EVENT_ASSET_DELETED_NAME,
            AssetAdmNotificationDecorator::getInstance($event->getDeleteId())
        );
    }
}
