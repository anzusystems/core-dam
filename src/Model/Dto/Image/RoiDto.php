<?php

declare(strict_types=1);

namespace App\Model\Dto\Image;

final readonly class RoiDto
{
    public function __construct(
        private int $pointX,
        private int $pointY,
        private float $percentageWidth,
        private float $percentageHeight
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

    public function getPercentageWidth(): float
    {
        return $this->percentageWidth;
    }

    public function getPercentageHeight(): float
    {
        return $this->percentageHeight;
    }
}
