<?php

declare(strict_types=1);

namespace App\Messenger\Handler;

use AnzuSystems\CoreDamBundle\Exception\RuntimeException;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use App\HttpClient\CloudFlareClient;
use App\HttpClient\NotificationClient;
use App\Messenger\Message\CdnPurgeMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CdnPurgeMessageHandler
{
    use MessageBusAwareTrait;

    public function __construct(
        private readonly CloudFlareClient $cloudFlareClient,
    ) {
    }

    public function __invoke(CdnPurgeMessage $message): void
    {
        $this->cloudFlareClient->purgeCdn($message->getPaths());
    }
}
