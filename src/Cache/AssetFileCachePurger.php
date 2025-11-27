<?php

declare(strict_types=1);

namespace App\Cache;

use AnzuSystems\CoreDamBundle\Cache\AssetFileCacheManager;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;

final readonly class AssetFileCachePurger
{
    public function __construct(
        private CachePurger $cachePurger,
    ) {
    }

    public function purge(AssetType $assetType, string $assetId): void
    {
        $this->cachePurger->addTag(AssetFileCacheManager::getAssetFileXKey($assetId), hard: true);
        $this->cachePurger->addTag(AssetFileCacheManager::getAssetFileXKeyPrefixed($assetType, $assetId), hard: true);
    }
}
