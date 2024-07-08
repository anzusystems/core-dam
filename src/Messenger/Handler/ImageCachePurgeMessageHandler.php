<?php

declare(strict_types=1);

namespace App\Messenger\Handler;

use AnzuSystems\CoreDamBundle\Cache\AssetFileCacheManager;
use AnzuSystems\CoreDamBundle\Exception\RuntimeException;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use App\Domain\Image\ImageRouteGenerator;
use App\HttpClient\NotificationClient;
use App\Messenger\Message\CdnPurgeMessage;
use App\Messenger\Message\ImageCachePurgeMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ImageCachePurgeMessageHandler
{
    use MessageBusAwareTrait;

    private const int MAX_CDN_PATHS = 30;

    public function __construct(
        private readonly NotificationClient $notificationClient,
        private readonly ImageRouteGenerator $imageRouteGenerator,
        private readonly bool $cacheProxyPurgeEnabled,
        private readonly bool $cdnPurgeEnabled,
    ) {
    }

    public function __invoke(ImageCachePurgeMessage $message): void
    {
        if ($this->cacheProxyPurgeEnabled) {
            $this->purgeCacheProxy($message);
        }

        if ($this->cdnPurgeEnabled) {
            $this->dispatchCdnPurge($message);
        }
    }

    private function purgeCacheProxy(ImageCachePurgeMessage $message): void
    {
        $response = $this->notificationClient->purgeCacheProxy(
            xKey: AssetFileCacheManager::getAssetFileXKey($message->getImageId()),
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

    private function dispatchCdnPurge(ImageCachePurgeMessage $message): void
    {
        // Admin paths is not cached in CF
        $paths = $this->imageRouteGenerator->generateAllPublicDomainPaths(
            extSystemSlug: $message->getExtSystemSlug(),
            imageId: $message->getImageId(),
            roiPositions: $message->getRoiPositions()
        );

        for ($i = 0; $i < count($paths); $i += self::MAX_CDN_PATHS) {
            $pathsBlock = array_slice($paths, $i, self::MAX_CDN_PATHS);

            $this->messageBus->dispatch(new CdnPurgeMessage($pathsBlock));
        }
    }
}
