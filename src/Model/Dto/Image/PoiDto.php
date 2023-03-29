<?php

declare(strict_types=1);

namespace App\Model\Dto\Image;

final readonly class PoiDto
{
    public function __construct(
        private int $pointX,
        private int $pointY,
        private int $imageWidth,
        private int $imageHeight
    ) {
    }

    public function getPointX(): int
    {
        return $this->pointX;
    }

    public function getPointY(): int
    {
        return $this->pointY;
    }

    public function getImageWidth(): int
    {
        return $this->imageWidth;
    }

    public function getImageHeight(): int
    {
        return $this->imageHeight;
    }

    public function isOnLeft(): bool
    {
        return ($this->imageWidth / 2) > $this->pointX;
    }

    public function isOnTheTop(): bool
    {
        return ($this->getImageHeight() / 2) > $this->pointY;
    }
}
