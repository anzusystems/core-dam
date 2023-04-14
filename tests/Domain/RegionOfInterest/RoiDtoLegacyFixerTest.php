<?php

declare(strict_types=1);


namespace App\Tests\Domain\RegionOfInterest;

use AnzuSystems\CommonBundle\Tests\AnzuKernelTestCase;
use AnzuSystems\CoreDamBundle\Entity\Embeds\ImageAttributes;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Model\Dto\RegionOfInterest\RegionOfInterestAdmDetailDto;
use App\Domain\RegionOfInterest\RoiDtoLegacyFixer;

final class RoiDtoLegacyFixerTest extends AnzuKernelTestCase
{
    protected RoiDtoLegacyFixer $fixer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixer = static::getContainer()->get(RoiDtoLegacyFixer::class);
    }

    public function testFixRoiDto(): void
    {
        $testImage = (new ImageFile())
            ->setImageAttributes(
                (new ImageAttributes())
                    ->setWidth(7435)
                    ->setHeight(4959)
            );

        $this->assertAndTest(
            (new RegionOfInterestAdmDetailDto())
                ->setPointX(0)
                ->setPointY(0)
                ->setPercentageWidth(0.5)
                ->setPercentageHeight(0.5)
                ->setImage($testImage),
            (new RegionOfInterestAdmDetailDto())
                ->setPointX(0)
                ->setPointY(0)
                ->setPercentageWidth(0.5)
                ->setPercentageHeight(0.5)
                ->setImage($testImage)
        );

        $this->assertAndTest(
            (new RegionOfInterestAdmDetailDto())
                ->setPointX(4305)
                ->setPointY(1599)
                ->setPercentageWidth(0.421287)
                ->setPercentageHeight(0.67855)
                ->setImage($testImage),
            (new RegionOfInterestAdmDetailDto())
                ->setPointX(4305)
                ->setPointY(1599)
                ->setPercentageWidth(0.42)
                ->setPercentageHeight(0.67)
                ->setImage($testImage),
        );

        $this->assertAndTest(
            (new RegionOfInterestAdmDetailDto())
                ->setPointX(7436)
                ->setPointY(-1)
                ->setPercentageWidth(0.1)
                ->setPercentageHeight(0.9)
                ->setImage($testImage),
            (new RegionOfInterestAdmDetailDto())
                ->setPointX(7435)
                ->setPointY(0)
                ->setPercentageWidth(0.0)
                ->setPercentageHeight(0.9)
                ->setImage($testImage)
        );
    }

    private function assertAndTest(RegionOfInterestAdmDetailDto $inputRoi, RegionOfInterestAdmDetailDto $expectedFixedRoi): void
    {
        $fixedRoi = $this->fixer->fixRoiDto($inputRoi);

        $this->assertSame($expectedFixedRoi->getPointX(), $fixedRoi->getPointX());
        $this->assertSame($expectedFixedRoi->getPointY(), $fixedRoi->getPointY());
        $this->assertSame($expectedFixedRoi->getPercentageWidth(), $fixedRoi->getPercentageWidth());
        $this->assertSame($expectedFixedRoi->getPercentageHeight(), $fixedRoi->getPercentageHeight());
    }
}