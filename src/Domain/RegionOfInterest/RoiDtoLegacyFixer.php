<?php

declare(strict_types=1);

namespace App\Domain\RegionOfInterest;

use AnzuSystems\CoreDamBundle\Model\Dto\RegionOfInterest\RegionOfInterestAdmDetailDto;

final class RoiDtoLegacyFixer
{
    public function fixRoiDto(RegionOfInterestAdmDetailDto $roiDto): RegionOfInterestAdmDetailDto
    {
        $imageWidth = $roiDto->getImage()->getImageAttributes()->getWidth();
        $imageHeight = $roiDto->getImage()->getImageAttributes()->getHeight();

        // fixed point X to be in range <0, imageWidth>
        $roiDto->setPointX(min(max(0, $roiDto->getPointX()), $imageWidth));
        // fixed point Y to be in range <0, imageWidth>
        $roiDto->setPointY(min(max(0, $roiDto->getPointY()), $imageHeight));

        $roiEndX = ((float) $roiDto->getPercentageWidth() * (float) $imageWidth) + (float) $roiDto->getPointX();
        if ($roiEndX > $imageWidth) {
            $roiDto->setPercentageWidth($this->round(
                ($imageWidth - $roiDto->getPointX()) / $imageWidth
            ));
        }

        $roiEndY = ((float) $roiDto->getPercentageHeight() * (float) $imageHeight) + (float) $roiDto->getPointY();
        if ($roiEndY > $imageHeight) {
            $roiDto->setPercentageHeight($this->round(($imageHeight - $roiDto->getPointY()) / $imageHeight));
        }

        return $roiDto;
    }

    private function round(float $value): float
    {
        return floor(((float) $value * 100.0)) / 100.0;
    }
}
