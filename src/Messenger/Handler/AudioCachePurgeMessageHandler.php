<?php

declare(strict_types=1);

namespace App\Messenger\Handler;

use AnzuSystems\CoreDamBundle\Cache\AssetFileCacheManager;
use AnzuSystems\CoreDamBundle\Cache\AudioRouteGenerator;
use AnzuSystems\CoreDamBundle\Exception\RuntimeException;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use App\HttpClient\NotificationClient;
use App\Messenger\Message\AudioCachePurgeMessage;
use App\Messenger\Message\CdnPurgeMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class AudioCachePurgeMessageHandler
{
    use MessageBusAwareTrait;

    public function __construct(
        private readonly NotificationClient $notificationClient,
        private readonly AudioRouteGenerator $audioRouteGenerator,
        private readonly bool $cacheProxyPurgeEnabled,
        private readonly bool $cdnPurgeEnabled,
    ) {
    }

    public function __invoke(AudioCachePurgeMessage $message): void
    {
        if ($this->cacheProxyPurgeEnabled) {
            $this->purgeCacheProxy($message);
        }

        if ($this->cdnPurgeEnabled) {
            $this->dispatchCdnPurge($message);
        }
    }

    private function purgeCacheProxy(AudioCachePurgeMessage $message): void
    {
        $response = $this->notificationClient->purgeCacheProxy(
            xKey: AssetFileCacheManager::getAssetFileXKey($message->getAudioId()),
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

    private function dispatchCdnPurge(AudioCachePurgeMessage $message): void
    {
        $this->messageBus->dispatch(
            new CdnPurgeMessage(paths: [
                $this->audioRouteGenerator->getFullUrl($message->getPath(), $message->getExtSystemSlug()),
            ])
        );
    }
}
