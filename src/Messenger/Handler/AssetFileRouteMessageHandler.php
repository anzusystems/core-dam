<?php

declare(strict_types=1);

namespace App\Messenger\Handler;

use AnzuSystems\CoreDamBundle\Cache\AssetFileCacheManager;
use AnzuSystems\CoreDamBundle\Cache\AssetFileRouteGenerator;
use AnzuSystems\CoreDamBundle\Exception\RuntimeException;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use App\HttpClient\NotificationClient;
use App\Messenger\Message\AssetFileRouteMessage;
use App\Messenger\Message\CdnPurgeMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class AssetFileRouteMessageHandler
{
    use MessageBusAwareTrait;

    public function __construct(
        private readonly NotificationClient $notificationClient,
        private readonly AssetFileRouteGenerator $assetFileRouteGenerator,
        private readonly bool $cacheProxyPurgeEnabled,
        private readonly bool $cdnPurgeEnabled,
    ) {
    }

    public function __invoke(AssetFileRouteMessage $message): void
    {
        if ($this->cacheProxyPurgeEnabled) {
            $this->purgeCacheProxy($message);
        }

        if ($this->cdnPurgeEnabled) {
            $this->dispatchCdnPurge($message);
        }
    }

    private function purgeCacheProxy(AssetFileRouteMessage $message): void
    {
        $response = $this->notificationClient->purgeCacheProxy(
            xKey: AssetFileCacheManager::getAssetFileXKey($message->getAssetFileId()),
        );

        if ($response->hasError()) {
            throw new RuntimeException(
                sprintf(
                    '[Anzu Notification] Purge cache proxy fails with status code: %s',
                    $response->getStatusCode(),
                )
            );
        }
    }

    private function dispatchCdnPurge(AssetFileRouteMessage $message): void
    {
        $this->messageBus->dispatch(
            new CdnPurgeMessage(paths: [
                $message->getFullUrl(),
            ])
        );
    }
}
