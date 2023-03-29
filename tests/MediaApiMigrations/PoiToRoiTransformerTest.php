<?php

declare(strict_types=1);


namespace App\Tests\MediaApiMigrations;

use AnzuSystems\CommonBundle\Tests\AnzuKernelTestCase;
use App\MediaApiMigrations\PoiToRoiTransformer;
use App\Model\Dto\Image\PoiDto;
use App\Model\Dto\Image\RoiDto;
use Jcupitt\Vips\BandFormat;
use Jcupitt\Vips\Extend;
use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;

final class PoiToRoiTransformerTest extends AnzuKernelTestCase
{
    private PoiToRoiTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transformer = static::getContainer()->get(PoiToRoiTransformer::class);
    }


    /**
     * @dataProvider transformPoiDataProvider
     */
    public function testTransformPoi(PoiDto $dto, RoiDto $expectedRoiDto): void
    {
        $roi = $this->transformer->transformPoi($dto);

        $this->assertSame($expectedRoiDto->getPointX(), $roi->getPointX());
        $this->assertSame($expectedRoiDto->getPointY(), $roi->getPointY());
        $this->assertSame($expectedRoiDto->getPercentageWidth(), $roi->getPercentageWidth());
        $this->assertSame($expectedRoiDto->getPercentageHeight(), $roi->getPercentageHeight());
    }

    public function transformPoiDataProvider(): array
    {
        return [
            [
                (new PoiDto(120, 50, 700, 200)),
                (new RoiDto(0, 0, 0.5071, 1)),
            ],
            [
                (new PoiDto(600, 50, 700, 200)),
                (new RoiDto(345, 0, 0.5071, 1)),
            ],
            [
                (new PoiDto(300, 130, 700, 200)),
                (new RoiDto(122, 0, 0.5071, 1)),
            ],
            [
                (new PoiDto(400, 130, 700, 200)),
                (new RoiDto(222, 0, 0.5071, 1)),
            ],
            [
                (new PoiDto(40, 50, 200, 400)),
                (new RoiDto(0, 0, 1, 0.28)),
            ],
            [
                (new PoiDto(120, 150, 200, 400)),
                (new RoiDto(0, 94, 1, 0.28)),
            ],
            [
                (new PoiDto(40, 300, 200, 400)),
                (new RoiDto(0, 244, 1, 0.28)),
            ],
            [
                (new PoiDto(120, 350, 200, 400)),
                (new RoiDto(0, 288, 1, 0.28)),
            ],
            [
                (new PoiDto(40, 70, 160, 90)),
                (new RoiDto(0, 0, 1, 1)),
            ],
            [
                (new PoiDto(90, 70, 160, 90)),
                (new RoiDto(0, 0, 1, 1)),
            ],
            [
                (new PoiDto(50, 50, 99, 99)),
                (new RoiDto(0, 22, 1, 0.5556)),
            ],
        ];
    }
}