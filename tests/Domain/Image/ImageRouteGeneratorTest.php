<?php

declare(strict_types=1);


namespace App\Tests\Domain\Image;

use AnzuSystems\CommonBundle\Tests\AnzuKernelTestCase;
use AnzuSystems\CoreDamBundle\DataFixtures\ImageFixtures as CoraDamBundleImageFixtures;
use AnzuSystems\CoreDamBundle\Repository\ImageFileRepository;
use App\DataFixtures\ImageFixtures;
use App\Domain\Image\ImageRouteGenerator;
use PHPUnit\Framework\Attributes\DataProvider;

final class ImageRouteGeneratorTest extends AnzuKernelTestCase
{
    private const CMS_ZERO_CROP_VALUES = [
        'http://image.smedata.localhost/image/w777-h777/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w777-h777-q90/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w777-h777-c0/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w777-h777-c0-q90/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w350-h197/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w350-h197-q90/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w350-h197-c0/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w350-h197-c0-q90/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w614-h345/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w614-h345-q90/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w614-h345-c0/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w614-h345-c0-q90/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w1000-h563/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w1000-h563-q90/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w1000-h563-c0/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w1000-h563-c0-q90/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w2000-h0/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w2000-h0-q90/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w2000-h0-c0/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w2000-h0-c0-q90/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w75-h75/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w75-h75-q90/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w75-h75-c0/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w75-h75-c0-q90/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w200-h200/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w200-h200-q90/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w200-h200-c0/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w200-h200-c0-q90/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w300-h300/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w300-h300-q90/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w300-h300-c0/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w300-h300-c0-q90/0d584443-2718-470a-b9b1-92d2d9c7447c.jpg',
    ];

    private const BLOG_ZERO_CROP_VALUES = [
        'http://blog-image.smedata.localhost/image/w972-h648/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
        'http://blog-image.smedata.localhost/image/w972-h648-q90/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
        'http://blog-image.smedata.localhost/image/w972-h648-c0/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
        'http://blog-image.smedata.localhost/image/w972-h648-c0-q90/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
        'http://blog-image.smedata.localhost/image/w600-h450/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
        'http://blog-image.smedata.localhost/image/w600-h450-q90/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
        'http://blog-image.smedata.localhost/image/w600-h450-c0/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
        'http://blog-image.smedata.localhost/image/w600-h450-c0-q90/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w123-h123/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w123-h123-q90/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w123-h123-c0/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w123-h123-c0-q90/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w123-h123-q70/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w123-h123-c0-q70/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
    ];

    private const BLOG_FIRST_CROP_VALUES = [
        'http://blog-image.smedata.localhost/image/w972-h648-c1/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
        'http://blog-image.smedata.localhost/image/w972-h648-c1-q90/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
        'http://blog-image.smedata.localhost/image/w600-h450-c1/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
        'http://blog-image.smedata.localhost/image/w600-h450-c1-q90/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w123-h123-c1/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w123-h123-c1-q90/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
        'http://image.smedata.localhost/image/w123-h123-c1-q70/1d584443-2718-470a-b9b1-10d2d9c7447c.jpg',
    ];

    protected ImageRouteGenerator $imageRouteGenerator;
    protected ImageFileRepository $imageFileRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->imageRouteGenerator = static::getContainer()->get(ImageRouteGenerator::class);
        $this->imageFileRepository = static::getContainer()->get(ImageFileRepository::class);
    }

    /**
     * @param array<int, int> $roiPositions
     * @param array<int, string> $expectedRoutes
     */
    #[DataProvider('generateAllPublicDomainPathsDataProvider')]
    public function testGenerateAllPublicDomainPaths(array $roiPositions, string $imageId, array $expectedRoutes): void
    {
        $image = $this->imageFileRepository->find($imageId);

        $paths = $this->imageRouteGenerator->generateAllPublicDomainPaths(
            extSystemSlug: $image->getLicence()->getExtSystem()->getSlug(),
            imageId: $imageId,
            roiPositions: $roiPositions
        );

        $this->assertEqualsCanonicalizing(
            $expectedRoutes,
            $paths
        );
    }

    public static function generateAllPublicDomainPathsDataProvider(): array
    {
        return [
            [
                [0],
                ImageFixtures::IMAGE_1_ID,
                self::BLOG_ZERO_CROP_VALUES
            ],
            [
                [1],
                ImageFixtures::IMAGE_1_ID,
                self::BLOG_FIRST_CROP_VALUES
            ],
            [
                [0, 1],
                ImageFixtures::IMAGE_1_ID,
                [...self::BLOG_ZERO_CROP_VALUES, ...self::BLOG_FIRST_CROP_VALUES]
            ],
            [
                [0],
                CoraDamBundleImageFixtures::IMAGE_ID_1_1,
                self::CMS_ZERO_CROP_VALUES
            ],
        ];
    }
}
