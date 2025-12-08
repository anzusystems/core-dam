<?php

declare(strict_types=1);

namespace App\Event\Listener;

use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Event\AssetFileDeleteEvent;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use App\Cache\AssetFileCachePurger;
use App\Cache\CacheCdnPurger;
use App\Cache\ImageFileUrlCdnPurger;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: AssetFileDeleteEvent::class)]
final class AssetFileDeleteListener
{
    use MessageBusAwareTrait;

    public function __construct(
        private readonly AssetFileCachePurger $imageCachePurger,
        private readonly CacheCdnPurger $cacheCdnPurger,
        private readonly ImageFileUrlCdnPurger $imageFileUrlCdnPurger,
    ) {
    }

    public function __invoke(AssetFileDeleteEvent $event): void
    {
        $assetFile = $event->getAssetFile();

        // todo validate sotuont
        $this->imageCachePurger->purge($assetFile->getAssetType(), (string) $assetFile->getId());

        // purga all image routes
        if ($assetFile instanceof ImageFile) {
            $this->imageFileUrlCdnPurger->purge(
                imageId: $event->getDeleteId(),
                roiPositions: $event->getRoiPositions(),
                extSystemSlug: $event->getExtSystem()
            );
        }

        // purge route paths
        foreach ($event->getRoutePaths() as $path) {
            $this->cacheCdnPurger->addUrl($path);
        }
    }
}
