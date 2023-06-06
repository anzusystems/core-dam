<?php

declare(strict_types=1);

namespace App\Serializer\Handler\Handlers;

use AnzuSystems\CoreDamBundle\Domain\Configuration\ConfigurationProvider;
use AnzuSystems\CoreDamBundle\Domain\Image\Crop\CropFactory;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageUrlFactory;
use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Entity\RegionOfInterest;
use AnzuSystems\CoreDamBundle\Model\Dto\Image\Crop\RequestedCropDto;
use AnzuSystems\CoreDamBundle\Model\Dto\Image\CropAllowItem;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\CoreDamBundle\Model\Enum\ImageCropTag;
use AnzuSystems\CoreDamBundle\Repository\RegionOfInterestRepository;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use AnzuSystems\SerializerBundle\Handler\Handlers\AbstractHandler;
use AnzuSystems\SerializerBundle\Metadata\Metadata;
use Doctrine\ORM\NonUniqueResultException;

final class LegacyUgcImageLinksHandler extends AbstractHandler
{
    public function __construct(
        private readonly CropFactory $cropFactory,
        private readonly RegionOfInterestRepository $roiRepository,
        private readonly ImageUrlFactory $imageUrlFactory,
        private readonly ConfigurationProvider $configurationProvider,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws SerializerException
     */
    public function serialize(mixed $value, Metadata $metadata): mixed
    {
        if (null === $metadata->customType) {
            return null;
        }

        if ($value instanceof ImageFile) {
            if ($value->getAssetAttributes()->getStatus()->isNot(AssetFileProcessStatus::Processed)) {
                return [];
            }

            $cropAllowItem = array_values(array_filter(
                $this->configurationProvider->getImageAdminSizeList($metadata->customType),
                static fn (CropAllowItem $cropAllowItem) => 200 === $cropAllowItem->getHeight() && 0 === $cropAllowItem->getWidth()
            ))[0] ?? null;

            if ($cropAllowItem instanceof CropAllowItem) {
                return $this->serializeCropAllowItem($value, $cropAllowItem);
            }
        }

        throw new SerializerException(sprintf('Value should be instance of (%s)', Asset::class));
    }

    /**
     * @throws SerializerException
     */
    public function deserialize(mixed $value, Metadata $metadata): mixed
    {
        throw new SerializerException('deserialize_not_supported');
    }

    /**
     * @throws NonUniqueResultException
     */
    private function serializeCropAllowItem(ImageFile $imageFile, CropAllowItem $item): array
    {
        $reqCrop = (new RequestedCropDto())
            ->setRequestWidth($item->getWidth())
            ->setRequestHeight($item->getHeight())
            ->setRoi(RegionOfInterest::FIRST_ROI_POSITION);

        $roi = $this->roiRepository->findByImageIdAndPosition(
            assetId: (string) $imageFile->getId(),
            roiPosition: RegionOfInterest::FIRST_ROI_POSITION,
        );
        if (null === $roi) {
            return [];
        }

        $cropDto = $this->cropFactory->prepareImageCrop($roi, $reqCrop, $imageFile);

        return [
            'url' => $this->configurationProvider->getAdminDomain() . $this->imageUrlFactory->generatePublicUrl(
                imageId: (string) $imageFile->getId(),
                width: $reqCrop->getRequestWidth(),
                height: $reqCrop->getRequestHeight(),
                roiPosition: $roi->getPosition()
            ),
            'width' => $cropDto->getRequestWidth(),
            'height' => $cropDto->getRequestHeight(),
        ];
    }
}
