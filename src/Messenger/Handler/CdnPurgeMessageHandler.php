<?php

declare(strict_types=1);

namespace App\Messenger\Handler;

use AnzuSystems\CoreDamBundle\Exception\RuntimeException;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use App\HttpClient\NotificationClient;
use App\Messenger\Message\CdnPurgeMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CdnPurgeMessageHandler
{
    use MessageBusAwareTrait;

    public function __construct(
        private readonly NotificationClient $notificationClient,
    ) {
    }

    public function __invoke(CdnPurgeMessage $message): void
    {
        $response = $this->notificationClient->purgeCdn($message->getPaths());
        if ($response->hasError()) {
            throw new RuntimeException(sprintf(
                '[Anzu Notification] Purge Purge CDN fails with status code: %s',
                $response->getStatusCode(),
            ));
        }
    }
}
