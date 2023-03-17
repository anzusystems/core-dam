<?php

declare(strict_types=1);

namespace App\DataFixtures;

use AnzuSystems\CoreDamBundle\DataFixtures\AbstractAssetFileFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\ImageFixtures as BaseImageFixtures;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileStatusFacadeProvider;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageFactory;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageManager;
use AnzuSystems\CoreDamBundle\Domain\RegionOfInterest\RegionOfInterestManager;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Entity\RegionOfInterest;
use AnzuSystems\CoreDamBundle\FileSystem\FileSystemProvider;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\CoreDamBundle\Repository\AssetLicenceRepository;
use Generator;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * @extends AbstractAssetFileFixtures<ImageFile>
 */
final class ImageFixtures extends AbstractAssetFileFixtures
{
    public const DATA_PATH = __DIR__ . '/../../vendor/anzusystems/core-dam-bundle/src/Resources/fixtures/';

    public const IMAGE_1_ID = '1d584443-2718-470a-b9b1-10d2d9c7447c';
    public const IMAGE_1_AUTHOR = 'Author Name 1';
    public const IMAGE_1_DESCRIPTION = 'Custom Data Description 1';
    public const IMAGE_2_ID = '2d584443-2718-470a-b9b1-10d2d9c7447c';
    public const IMAGE_2_AUTHOR = 'Author Name 2';
    public const IMAGE_2_DESCRIPTION = 'Custom Data Description 2';

    public function __construct(
        private readonly ImageManager $imageManager,
        private readonly ImageFactory $imageFactory,
        private readonly AssetLicenceRepository $licenceRepository,
        private readonly FileSystemProvider $fileSystemProvider,
        private readonly AssetFileStatusFacadeProvider $facadeProvider,
        private readonly RegionOfInterestManager $regionOfInterestManager,
    ) {
    }

    public static function getIndexKey(): string
    {
        return ImageFile::class;
    }

    public static function getDependencies(): array
    {
        return [BaseImageFixtures::class, AssetLicenceFixtures::class];
    }

    public function useCustomId(): bool
    {
        return true;
    }

    public function load(ProgressBar $progressBar): void
    {
        /** @var ImageFile $image */
        foreach ($progressBar->iterate($this->getData()) as $image) {
            $image = $this->imageManager->create($image);
            $this->addToRegistry($image, (int) $image->getId());
        }
    }

    private function getData(): Generator
    {
        $fileSystem = $this->fileSystemProvider->createLocalFilesystem(self::DATA_PATH);
        /** @var AssetLicence $licence */
        $licence = $this->licenceRepository->find(AssetLicenceFixtures::BLOG_DEFAULT_ASSET_LICENCE_ID);

        $file = $this->getFile($fileSystem, 'text_image_108x192.png');
        $image = $this->imageFactory->createFromFile(
            $file,
            $licence,
            self::IMAGE_1_ID
        );
        $image->getAsset()->getMetadata()->setCustomData([
            'author' => self::IMAGE_1_AUTHOR,
            'description' => self::IMAGE_1_DESCRIPTION,
        ]);
        $image->getAssetAttributes()->setStatus(AssetFileProcessStatus::Uploaded);
        $image->getAsset()->getAssetFlags()->setDescribed(true);
        $this->facadeProvider->getStatusFacade($image)->storeAndProcess($image, $file);
        $image->getRegionsOfInterest()->add(
            $this->regionOfInterestManager->create(
                (new RegionOfInterest())
                    ->setImage($image)
                    ->setPointX(0)
                    ->setPointY(0)
                    ->setPercentageWidth(0.5)
                    ->setPercentageHeight(0.5)
            )
        );

        yield $image;

        $file = $this->getFile($fileSystem, 'text_image_200x200.jpg');
        $image = $this->imageFactory->createFromFile(
            $file,
            $licence,
            self::IMAGE_2_ID
        );
        $image->getAsset()->getMetadata()->setCustomData([
            'author' => self::IMAGE_2_AUTHOR,
            'description' => self::IMAGE_2_DESCRIPTION,
        ]);
        $image->getAssetAttributes()->setStatus(AssetFileProcessStatus::Uploaded);
        $image->getAsset()->getAssetFlags()->setDescribed(true);
        $this->facadeProvider->getStatusFacade($image)->storeAndProcess($image, $file);

        yield $image;
    }
}
