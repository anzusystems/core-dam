<?php

declare(strict_types=1);


namespace App\Notification;

use AnzuSystems\CommonBundle\Domain\User\CurrentAnzuUserProvider;
use AnzuSystems\CommonBundle\Traits\SerializerAwareTrait;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractNotificationDispatcher
{
    use SerializerAwareTrait;

    protected CurrentAnzuUserProvider $currentUserProvider;

    #[Required]
    public function setCurrentUserProvider(CurrentAnzuUserProvider $currentUserProvider): void
    {
        $this->currentUserProvider = $currentUserProvider;
    }

    /**
     * @param list<int> $userIds
     *
     * @throws SerializerException
     */
    protected function notify(array $userIds, string $eventName, object $data): void
    {
        $pubSubClient = new PubSubClient();
        // todo move topic name to env.
        $pubSubClient->topic('notification_server_internal')->publish(
            new Message([
                'attributes' => [
                    'targetSsoUserIds' => json_encode([3]),
                    'eventName' => $eventName,
                ],
                'data' => $this->serializer->serialize($data)
            ])
        );
    }
}
