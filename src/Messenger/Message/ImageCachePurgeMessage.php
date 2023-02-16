<?php

declare(strict_types=1);

namespace App\Messenger\Message;

final readonly class ImageCachePurgeMessage
{
    public function __construct(
        private string $imageId,
        private array $roiPositions,
        private string $extSystemSlug,
    ) {
    }

    public function getImageId(): string
    {
        return $this->imageId;
    }

    public function getRoiPositions(): array
    {
        return $this->roiPositions;
    }

    public function getExtSystemSlug(): string
    {
        return $this->extSystemSlug;
    }
}
