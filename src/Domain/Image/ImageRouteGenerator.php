<?php

declare(strict_types=1);

namespace App\Domain\Image;

use AnzuSystems\CoreDamBundle\Domain\Configuration\AllowListConfiguration;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageUrlFactory;
use AnzuSystems\CoreDamBundle\Helper\UrlHelper;
use AnzuSystems\CoreDamBundle\Model\Configuration\AllowListMapConfiguration;

final readonly class ImageRouteGenerator
{
    public function __construct(
        private AllowListConfiguration $allowListConfiguration,
        private ImageUrlFactory $imageUrlFactory,
    ) {
    }

    public function generateAllPublicDomainPaths(string $extSystemSlug, string $imageId, array $roiPositions = []): array
    {
        $paths = [];
        /** @var AllowListMapConfiguration $map */
        foreach ($this->allowListConfiguration->getSlugAllowLists($extSystemSlug) as $map) {
            $domain = $this->allowListConfiguration->getCacheConfiguration($map->getDomainKey());
            if ($domain->isPublic()) {
                $paths = [
                    ...$paths,
                    ...$this->generateDomainPaths(
                        extSystemSlug: $extSystemSlug,
                        domain: $domain->getDomain(),
                        imageId: $imageId,
                        roiPositions: $roiPositions
                    ),
                ];
            }
        }

        return array_values(array_unique($paths));
    }

    private function generateDomainPaths(
        string $extSystemSlug,
        string $domain,
        string $imageId,
        array $roiPositions = []
    ): array {
        $paths = [];
        $list = $this->allowListConfiguration->getListByDomain(
            extSystemSlug: $extSystemSlug,
            domain: $domain
        );

        /** @var array<int, int|null> $qualityList */
        $qualityList = [null, ...$list->getQualityAllowList()];
        $roiList = $this->getRoiList($roiPositions);

        foreach ($list->getCrops() as $crop) {
            foreach ($roiList as $roiPosition) {
                foreach ($qualityList as $quality) {
                    $paths[] = $this->generatePath(
                        imageId: $imageId,
                        domain: $domain,
                        width: $crop['width'],
                        height: $crop['height'],
                        roiPosition: $roiPosition,
                        quality: $quality
                    );
                }
            }
        }

        return $paths;
    }

    /**
     * @return array<int, int|null>
     */
    private function getRoiList(array $roiPositions): array
    {
        if (in_array(0, $roiPositions, true)) {
            return [null, ...$roiPositions];
        }

        return $roiPositions;
    }

    private function generatePath(
        string $imageId,
        string $domain,
        int $width,
        int $height,
        ?int $roiPosition = null,
        ?int $quality = null
    ): string {
        return UrlHelper::concatPathWithDomain(
            $domain,
            $this->imageUrlFactory->generatePublicUrl(
                imageId: $imageId,
                width: $width,
                height: $height,
                roiPosition: $roiPosition,
                quality: $quality
            )
        );
    }
}
