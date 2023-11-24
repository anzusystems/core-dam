<?php

declare(strict_types=1);

namespace App\Model\Domain\AssetMetadata;

use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class MediaApiMetadata extends ImageMetadata
{
    #[Serialize]
    private array $mediaApiIds = [];

    #[Serialize]
    private array $mediaApiPaths = [];

    #[Serialize]
    private int $focusX = 0;

    #[Serialize]
    private int $focusY = 0;

    public function getMediaApiIds(): array
    {
        return $this->mediaApiIds;
    }

    public function setMediaApiIds(array $mediaApiIds): self
    {
        $this->mediaApiIds = $mediaApiIds;
        return $this;
    }

    public function getMediaApiPaths(): array
    {
        return $this->mediaApiPaths;
    }

    public function setMediaApiPaths(array $mediaApiPaths): self
    {
        $this->mediaApiPaths = $mediaApiPaths;
        return $this;
    }

    public function getFocusX(): int
    {
        return $this->focusX;
    }

    public function setFocusX(int $focusX): self
    {
        $this->focusX = $focusX;
        return $this;
    }

    public function getFocusY(): int
    {
        return $this->focusY;
    }

    public function setFocusY(int $focusY): self
    {
        $this->focusY = $focusY;
        return $this;
    }

    public function addMediaApiId(int $mediaApiId): self
    {
        if (false === in_array($mediaApiId, $this->mediaApiIds, true)) {
            $this->mediaApiIds[] = $mediaApiId;
        }

        return $this;
    }

    public function addMediaApiPath(string $path): self
    {
        if (false === in_array($path, $this->mediaApiPaths, true)) {
            $this->mediaApiPaths[] = $path;
        }

        return $this;
    }
}
