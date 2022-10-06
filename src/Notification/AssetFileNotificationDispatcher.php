<?php

declare(strict_types=1);


namespace App\Notification;

use AnzuSystems\CommonBundle\Traits\SerializerAwareTrait;
use AnzuSystems\CoreDamBundle\Event\AssetFileChangeStateEvent;
use AnzuSystems\CoreDamBundle\Event\AssetFileDeleteEvent;
use AnzuSystems\CoreDamBundle\Event\MetadataProcessedEvent;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Model\Domain\AssetFile\AsseFileAdmNotificationDecorator;
use App\Model\Domain\AssetFile\AssetFileStatusAdmNotificationDecorator;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;

final class AssetFileNotificationDispatcher extends AbstractNotificationDispatcher
{
    private const EVENT_NAME_PREFIX = 'asset_file_';
    private const EVENT_METADATA_PROCESSED_NAME = 'asset_metadata_processed';
    private const EVENT_ASSET_FILE_DELETED_NAME = 'asset_file_deleted';

    use SerializerAwareTrait;

    /**
     * @throws SerializerException
     */
    public function notifyAssetFileDeleted(AssetFileDeleteEvent $event): void
    {
        $this->notify(
            [$event->getDeletedBy()->getId()],
            self::EVENT_ASSET_FILE_DELETED_NAME,
            AsseFileAdmNotificationDecorator::getBaseInstance($event->getAssetId(), $event->getDeleteId())
        );
    }

    /**
     * @throws SerializerException
     */
    public function notifyAssetFileChanged(AssetFileChangeStateEvent $event): void
    {
        $this->notify(
            [$event->getAsset()->getCreatedBy()->getId()],
            self::EVENT_NAME_PREFIX . $event->getAsset()->getAssetAttributes()->getStatus()->toString(),
            AssetFileStatusAdmNotificationDecorator::getInstance($event->getAsset())
        );
    }

    /**
     * @throws SerializerException
     */
    public function notifyMetadataProcessed(MetadataProcessedEvent $event): void
    {
        $this->notify(
            [$event->getAsset()->getCreatedBy()->getId()],
            self::EVENT_METADATA_PROCESSED_NAME,
            AssetFileStatusAdmNotificationDecorator::getInstance($event->getAsset())
        );
    }
}
