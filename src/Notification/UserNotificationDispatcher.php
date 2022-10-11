<?php

declare(strict_types=1);


namespace App\Notification;

use AnzuSystems\SerializerBundle\Exception\SerializerException;

final class UserNotificationDispatcher extends AbstractNotificationDispatcher
{
    private const EVENT_NAME = 'user_updated';

    /**
     * @throws SerializerException
     */
    public function notifyUserUpdated(int $userId): void
    {
        $this->notify([$userId], self::EVENT_NAME);
    }
}
