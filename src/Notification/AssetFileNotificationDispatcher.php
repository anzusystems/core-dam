<?php

declare(strict_types=1);


namespace App\Notification;

use AnzuSystems\CommonBundle\Traits\SerializerAwareTrait;
use AnzuSystems\CoreDamBundle\Event\AssetFileChangeStateEvent;
use AnzuSystems\CoreDamBundle\Event\MetadataProcessedEvent;
use App\Model\Domain\AssetFile\AssetFileAdmNotificationDecorator;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;

final class AssetFileNotificationDispatcher
{
    use SerializerAwareTrait;

    public function notifyAssetFileChanged(AssetFileChangeStateEvent $event): void
    {
        $this->notify(
            [$event->getAsset()->getCreatedBy()->getId()],
            'asset_' . $event->getAsset()->getAssetAttributes()->getStatus()->toString(),
            AssetFileAdmNotificationDecorator::getInstance($event->getAsset())
        );
    }

    public function notifyMetadataProcessed(MetadataProcessedEvent $event): void
    {
        $this->notify(
            [$event->getAsset()->getCreatedBy()->getId()],
            'asset_metadata_processed',
            AssetFileAdmNotificationDecorator::getInstance($event->getAsset())
        );
    }

    private function notify(array $userIds, string $eventName, object $data): void
    {
        $pubSubClient = new PubSubClient();
        // todo move topic name to env.
        $pubSubClient->topic('notification_server_internal')->publish(
            new Message([
                'attributes' => [
                    'targetSsoUserIds' => json_encode($userIds),
                    'eventName' => $eventName,
                ],
                'data' => $this->serializer->serialize($data)
            ])
        );
    }
}