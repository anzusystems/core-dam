<?php

declare(strict_types=1);

namespace App\Distribution\Modules\Factory;

use AnzuSystems\CoreDamBundle\Distribution\AbstractDistributionDtoFactory;
use AnzuSystems\CoreDamBundle\Domain\Configuration\ConfigurationProvider;
use AnzuSystems\CoreDamBundle\Domain\Image\ImageUrlFactory;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use App\Model\Dto\Artemis\ArtemisImageDto;
use App\Model\Dto\Artemis\ArtemisMediaAuthorDto;
use App\Model\Dto\Artemis\ArtemisMediaTagDto;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractArtemisDtoFactory extends AbstractDistributionDtoFactory
{
    private const ARTEMIS_DISTRIBUTION_TAG = 'artemis_distribution';

    protected ConfigurationProvider $configurationProvider;
    protected ImageUrlFactory $imageUrlFactory;

    #[Required]
    public function setImageUrlFactory(ImageUrlFactory $imageUrlFactory): void
    {
        $this->imageUrlFactory = $imageUrlFactory;
    }

    #[Required]
    public function setConfigurationProvider(ConfigurationProvider $configurationProvider): void
    {
        $this->configurationProvider = $configurationProvider;
    }

    protected function getImage(ImageFile $assetFile): ?ArtemisImageDto
    {
        $cropList = $this->configurationProvider->getImageAdminSizeList(self::ARTEMIS_DISTRIBUTION_TAG);
        $cropAllowItem = reset($cropList) ?: null;

        if ($cropAllowItem) {
            return (new ArtemisImageDto())
                ->setUrl(
                    $this->configurationProvider->getAdminDomain() . $this->imageUrlFactory->generatePublicUrl(
                        imageId: (string) $assetFile->getId(),
                        width: $cropAllowItem->getWidth(),
                        height: $cropAllowItem->getHeight(),
                    )
                )
                ->setTitle($assetFile->getAsset()->getTexts()->getDisplayTitle())
            ;
        }

        return null;
    }

    protected function transformKeywords(array $keywords): array
    {
        $artemisKeywords = [];
        foreach ($keywords as $keyword) {
            $artemisKeywords[] = (new ArtemisMediaTagDto())
                ->setTitle($keyword);
        }

        return $artemisKeywords;
    }

    protected function transformAuthors(array $authors): array
    {
        $artemisAuthors = [];
        foreach ($authors as $author) {
            $artemisAuthors[] = (new ArtemisMediaAuthorDto())
                ->setFullName($author);
        }

        return $artemisAuthors;
    }
}
