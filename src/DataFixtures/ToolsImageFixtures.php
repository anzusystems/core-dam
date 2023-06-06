<?php

declare(strict_types=1);

namespace App\DataFixtures;

use AnzuSystems\CoreDamBundle\DataFixtures\AbstractAssetFileFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\ImageFixtures as BaseImageFixtures;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileStatusFacadeProvider;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageFactory;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageManager;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\FileSystem\FileSystemProvider;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\CoreDamBundle\Repository\AssetLicenceRepository;
use Generator;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * @extends AbstractAssetFileFixtures<ImageFile>
 */
final class ToolsImageFixtures extends AbstractAssetFileFixtures
{
    public const DATA_PATH = __DIR__ . '/../../tests/data/files/';

    public const NOT_FOUND_IMAGE_ID = 'c41ca3a7-af73-46ee-a517-5f3748815c01';

    private const TOOLS_LICENCE_ID = 200_000;

    public function __construct(
        private readonly ImageManager $imageManager,
        private readonly ImageFactory $imageFactory,
        private readonly AssetLicenceRepository $licenceRepository,
        private readonly FileSystemProvider $fileSystemProvider,
        private readonly AssetFileStatusFacadeProvider $facadeProvider,
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
        $licence = $this->licenceRepository->find(self::TOOLS_LICENCE_ID);

        $file = $this->getFile($fileSystem, 'not_found.jpg');
        $image = $this->imageFactory->createFromFile(
            $file,
            $licence,
            self::NOT_FOUND_IMAGE_ID
        );
        $image->getAssetAttributes()->setStatus(AssetFileProcessStatus::Uploaded);
        $image->getAsset()->getAssetFlags()->setDescribed(true);
        $this->facadeProvider->getStatusFacade($image)->storeAndProcess($image, $file);

        yield $image;
    }
}
