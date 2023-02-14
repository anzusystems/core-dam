<?php

declare(strict_types=1);

namespace App\Distribution\Modules\Factory;

use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Entity\JwDistribution;
use AnzuSystems\CoreDamBundle\Entity\VideoFile;
use AnzuSystems\CoreDamBundle\Entity\YoutubeDistribution;
use AnzuSystems\CoreDamBundle\Repository\DistributionRepository;
use App\Entity\ArtemisVideoDistribution;
use App\Model\Dto\Artemis\ArtemisMediaDto;
use App\Model\Dto\Artemis\ArtemisMediaRubricDto;

final class ArtemisVideoDtoFactory extends AbstractArtemisDtoFactory
{
    public function __construct(
        private readonly DistributionRepository $repository,
    ) {
    }

    public function createMediaDto(
        VideoFile $assetFile,
        ArtemisVideoDistribution $distribution,
    ): ArtemisMediaDto {
        $mediaDto = new ArtemisMediaDto();
        $mediaDto
            ->setTitle($distribution->getTexts()->getTitle())
            ->setDescription($distribution->getTexts()->getDescription())
            ->setCreateArticle($distribution->getFlags()->isCreateArticle())
            ->setDuration($assetFile->getAttributes()->getDuration());
        $mediaDto->setAuthors($this->transformAuthors($distribution->getTexts()->getAuthors()));
        $mediaDto->setTags($this->transformKeywords($distribution->getTexts()->getKeywords()));
        $mediaDto->setRubric((new ArtemisMediaRubricDto())->setId($distribution->getTexts()->getRubricId()));
        $previewImage = $assetFile->getImagePreview()?->getImageFile();
        if ($previewImage instanceof ImageFile) {
            $mediaDto->setImage($this->getImage($previewImage));
        }

        $distributions = $this->repository->findByAssetFile((string) $assetFile->getId());
        foreach ($distributions as $distribution) {
            if ($distribution instanceof JwDistribution && false === empty($distribution->getExtId())) {
                $mediaDto->setJwId($distribution->getExtId());
            }
            if ($distribution instanceof YoutubeDistribution && false === empty($distribution->getExtId())) {
                $mediaDto->setYoutubeId($distribution->getExtId());
            }
        }

        return $mediaDto;
    }
}
