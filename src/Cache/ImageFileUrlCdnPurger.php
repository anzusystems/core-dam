<?php

declare(strict_types=1);

namespace App\Cache;

use AnzuSystems\CoreDamBundle\Cache\AssetFileCacheManager;
use AnzuSystems\CoreDamBundle\Domain\Configuration\ExtSystemConfigurationProvider;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use App\Domain\Image\ImageRouteGenerator;

final readonly class ImageFileUrlCdnPurger
{
    public function __construct(
        private CacheCdnPurger $cacheCdnPurger,
        private ImageRouteGenerator $imageRouteGenerator,
        private ExtSystemConfigurationProvider $extSystemConfigurationProvider,
    ) {
    }

    /**
     * @param array<array-key, int> $roiPositions
     */
    public function purge(
        string $imageId,
        array $roiPositions,
        string $extSystemSlug,
    ): void {
        // Admin paths is not cached in CF
        $paths = $this->imageRouteGenerator->generateAllPublicDomainPaths(
            extSystemSlug: $extSystemSlug,
            imageId: $imageId,
            roiPositions: $roiPositions
        );

        $configuration = $this->extSystemConfigurationProvider->getImageExtSystemConfiguration(
            $extSystemSlug
        );
        $this->cacheCdnPurger->addTag($configuration->getPublicDomain(), AssetFileCacheManager::getAssetFileXKeyPrefixed(
            AssetType::Image,
            $imageId
        ));

        foreach ($paths as $path) {
            $this->cacheCdnPurger->addUrl($path);
        }
    }
}
