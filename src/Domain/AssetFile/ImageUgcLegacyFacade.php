<?php

declare(strict_types=1);

namespace App\Domain\AssetFile;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\Contracts\Exception\AnzuException;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AbstractAssetFileFacade;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileFactory;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileManager;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageFacade;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageFactory;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageManager;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Model\Dto\Image\ImageAdmCreateDto;
use AnzuSystems\CoreDamBundle\Repository\AbstractAssetFileRepository;
use AnzuSystems\CoreDamBundle\Repository\ImageFileRepository;
use AnzuSystems\CoreDamBundle\Security\AccessDenier;
use App\Exception\DuplicateImageFileException;
use App\Model\Ugc\Legacy\ImageCreateDto;
use App\Model\Ugc\Legacy\ImageUpdateDto;
use App\Security\Voter\UgcVoter;
use App\Validator\IterableValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use RuntimeException;

/**
 * @extends AbstractAssetFileFacade<ImageFile>
 */
final class ImageUgcLegacyFacade extends AbstractAssetFileFacade
{
    public function __construct(
        private readonly ImageManager $imageManager,
        private readonly ImageFactory $imageFactory,
        private readonly ImageFileRepository $imageFileRepository,
        private readonly ImageFacade $imageFacade,
        private readonly AccessDenier $accessDenier,
        private readonly ImageUgcLegacyManager $imageUgcLegacyManager,
        private readonly IterableValidator $iterableValidator,
    ) {
    }

    /**
     * @throws RuntimeException
     */
    public function rotateImage(ImageFile $image, float $angle): ImageFile
    {
        return $this->imageFacade->rotateImage($image, $angle);
    }

    /**
     * @throws ValidationException
     * @throws NonUniqueResultException
     * @throws DuplicateImageFileException
     */
    public function create(ImageCreateDto $image, AssetLicence $licence): ImageFile
    {
        $this->validator->validate($image);

        $originAsset = $this->imageFileRepository->findProcessedByChecksumAndLicence(
            checksum: $image->getFileAttributes()->getPartialChecksum(),
            licence: $licence,
        );
        if ($originAsset instanceof ImageFile) {
            throw new DuplicateImageFileException($originAsset);
        }

        $imageDto = (new ImageAdmCreateDto())
            ->setMimeType($image->getFileAttributes()->getMimeType())
            ->setChecksum($image->getFileAttributes()->getPartialChecksum())
            ->setSize($image->getFileAttributes()->getSize())
        ;

        return $this->createAssetFile($imageDto, $licence);
    }

    /**
     * @param ArrayCollection<int, ImageUpdateDto> $imageUpdateFiles
     *
     * @return ArrayCollection<int, ImageFile>
     *
     * @throws AnzuException
     * @throws ValidationException
     */
    public function updateBulk(ArrayCollection $imageUpdateFiles, bool $onlyUndescribed): ArrayCollection
    {
        $this->iterableValidator->validateDtoItems($imageUpdateFiles);
        /** @var ArrayCollection<int, ImageFile> $updatedImages */
        $updatedImages = new ArrayCollection();
        foreach ($imageUpdateFiles as $imageUpdateFile) {
            $updatedImage = $this->update($imageUpdateFile, $onlyUndescribed, false);
            $updatedImages->add($updatedImage);
        }
        if (false === $updatedImages->isEmpty()) {
            $this->imageUgcLegacyManager->flush();
            foreach ($updatedImages as $updatedImage) {
                $this->indexManager->index($updatedImage->getAsset());
            }
        }

        return $updatedImages;
    }

    /**
     * @throws AnzuException
     */
    public function update(ImageUpdateDto $imageUpdateDto, bool $onlyUndescribed, bool $flush = true): ImageFile
    {
        if ($flush) {
            $this->validator->validate($imageUpdateDto);
        }
        $oldImageFile = $this->imageFileRepository->find($imageUpdateDto->getId());
        if (null === $oldImageFile) {
            throw new AnzuException(sprintf('Image file (%s) not found', $imageUpdateDto->getId()));
        }
        $this->accessDenier->denyUnlessGranted(UgcVoter::DAM_UGC_ACCESS, $oldImageFile);

        $updatedImage = null;
        if (false === $onlyUndescribed || $oldImageFile->getAsset()->getAssetFlags()->isNotDescribed()) {
            $updatedImage = $this->imageUgcLegacyManager->updateUgcImage($oldImageFile, $imageUpdateDto, $flush);
        }

        if ($flush && $updatedImage instanceof ImageFile) {
            $this->imageUgcLegacyManager->flush();
            $this->indexManager->index($updatedImage->getAsset());
        }

        return $updatedImage ?? $oldImageFile;
    }

    protected function getManager(): AssetFileManager
    {
        return $this->imageManager;
    }

    protected function getFactory(): AssetFileFactory
    {
        return $this->imageFactory;
    }

    protected function getRepository(): AbstractAssetFileRepository
    {
        return $this->imageFileRepository;
    }
}
