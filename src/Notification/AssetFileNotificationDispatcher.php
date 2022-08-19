<?php

declare(strict_types=1);


namespace App\Notification;

use Anzu\CommonBundle\AnzuSerializer\Exception\AnzuSerializerException;
use Anzu\CommonBundle\Traits\AnzuSerializerAwareTrait;
use AnzuSystems\CoreDamBundle\Event\AssetFileChangeStateEvent;
use App\Model\Domain\AssetFile\AssetFileAdmNotificationDecorator;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;

final class AssetFileNotificationDispatcher
{
    use AnzuSerializerAwareTrait;

    /**
     * @throws AnzuSerializerException
     */
    public function notifyAssetFileChanged(AssetFileChangeStateEvent $event): void
    {
        $this->notify(
            [$event->getAsset()->getCreatedBy()->getId()],
            'asset_' . $event->getAsset()->getAssetAttributes()->getStatus()->toString(),
            AssetFileAdmNotificationDecorator::getInstance($event->getAsset())
        );
    }

    /**
     * @throws AnzuSerializerException
     */
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
                'data' => $this->anzuSerializer->serialize($data)
            ])
        );
    }
}