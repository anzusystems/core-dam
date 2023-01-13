<?php

declare(strict_types=1);

namespace App\Domain\AssetFile;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileFacade;
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
use App\Exception\DuplicateImageFileException;
use App\Model\Ugc\Legacy\ImageCreateDto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use RuntimeException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @extends AssetFileFacade<ImageFile>
 */
final class ImageUgcLegacyFacade extends AssetFileFacade
{
    public function __construct(
        private readonly ImageManager $imageManager,
        private readonly ImageFactory $imageFactory,
        private readonly ImageFileRepository $imageFileRepository,
        private readonly ImageFacade $imageFacade,
        private readonly ValidatorInterface $validator
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
        $this->entityValidator->validateDto($image);

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
     * @throws ValidationException
     */
    public function updateBulk(ArrayCollection $newImageFiles, bool $onlyUndescribed): ArrayCollection
    {
        $violationList = new ConstraintViolationList();
        $violationList->addAll(
            $this->validator->validate($newImageFiles)
        );
        if ($violationList->count()) {
            throw new ValidationException($violationList);
        }

        $test = null;
        $result = new ArrayCollection();

        return $result;
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
