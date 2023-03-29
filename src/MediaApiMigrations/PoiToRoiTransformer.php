<?php

declare(strict_types=1);

namespace App\MediaApiMigrations;

use App\Model\Dto\Image\PoiDto;
use App\Model\Dto\Image\RoiDto;

final class PoiToRoiTransformer
{
    private const DEFAULT_ROI_WIDTH = 16;
    private const DEFAULT_ROI_HEIGHT = 9;

    public function transformPoi(PoiDto $dto): RoiDto
    {
        $roiRatio = self::DEFAULT_ROI_WIDTH / self::DEFAULT_ROI_HEIGHT;
        $imageRatio = $dto->getImageWidth() / $dto->getImageHeight();

        // image is higher
        if ($roiRatio > $imageRatio) {
            $roiWidth = $dto->getImageWidth();
            $roiHeight = floor($dto->getImageWidth() / $roiRatio);
            $potentialY = floor($dto->getPointY() - ($roiHeight / 2));

            return new RoiDto(
                pointX: 0,
                pointY: $dto->isOnTheTop()
                    ? (int) max($potentialY, 0)
                    : (int) min($potentialY, $dto->getImageHeight() - $roiHeight),
                percentageWidth: $this->getPercentageOfValue($roiWidth, $dto->getImageWidth()),
                percentageHeight: $this->getPercentageOfValue($roiHeight, $dto->getImageHeight()),
            );
        }

        // image is wider
        if ($roiRatio < $imageRatio) {
            $roiHeight = $dto->getImageHeight();
            $roiWidth = floor($dto->getImageHeight() * $roiRatio);
            $potentialX = floor($dto->getPointX() - ($roiWidth / 2));

            return new RoiDto(
                pointX: $dto->isOnLeft()
                    ? (int) max($potentialX, 0)
                    : (int) min($potentialX, $dto->getImageWidth() - $roiWidth),
                pointY: 0,
                percentageWidth: $this->getPercentageOfValue($roiWidth, $dto->getImageWidth()),
                percentageHeight: $this->getPercentageOfValue($roiHeight, $dto->getImageHeight()),
            );
        }

        return new RoiDto(
            pointX: 0,
            pointY: 0,
            percentageWidth: 1,
            percentageHeight: 1,
        );
    }

    private function getValueOrMin(int $value, int $min): int
    {
        return max($value, $min);
    }

    private function getPercentageOfValue(float $roiSize, float $totalSize): float
    {
        return round($roiSize / $totalSize, 4);
    }
}
