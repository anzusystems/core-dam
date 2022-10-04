<?php

declare(strict_types=1);


namespace App\Notification;

use AnzuSystems\CommonBundle\Traits\SerializerAwareTrait;
use AnzuSystems\CoreDamBundle\Event\AssetDeleteEvent;
use AnzuSystems\CoreDamBundle\Event\AssetFileChangeStateEvent;
use AnzuSystems\CoreDamBundle\Event\MetadataProcessedEvent;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Model\Domain\AssetFile\AssetAdmNotificationDecorator;
use App\Model\Domain\AssetFile\AssetFileStatusAdmNotificationDecorator;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;

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
            [$this->currentUserProvider->getCurrentUser()],
            self::EVENT_ASSET_DELETED_NAME,
            AssetAdmNotificationDecorator::getInstance($event->getDeleteId())
        );
    }
}
