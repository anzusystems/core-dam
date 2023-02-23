<?php

declare(strict_types=1);

namespace App\Distribution\Modules\Factory;

use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use App\Entity\ArtemisAudioDistribution;
use App\Model\Dto\Artemis\ArtemisAudioMediaDto;
use App\Model\Dto\Artemis\ArtemisMediaChannel;
use App\Model\Dto\Artemis\ArtemisMediaRubricDto;

final class ArtemisAudioDtoFactory extends AbstractArtemisDtoFactory
{
    public function createMediaDto(
        AudioFile $assetFile,
        ArtemisAudioDistribution $distribution,
    ): ArtemisAudioMediaDto {
        $mediaDto = new ArtemisAudioMediaDto();
        $mediaDto
            ->setTitle($distribution->getTexts()->getTitle())
            ->setDescription($distribution->getTexts()->getDescription())
            ->setAnzuMediaId((string) $assetFile->getAsset()->getId())
            ->setPremiumSourceUrl($distribution->getTexts()->getPremiumUrl())
            ->setDirectSourceUrl($distribution->getTexts()->getFreeUrl())
            ->setCreateArticle($distribution->getFlags()->isCreateArticle())
            ->setBonus($distribution->getFlags()->isBonusEpisode())
            ->setMediaChannel((new ArtemisMediaChannel())->setAnzuId($distribution->getTexts()->getPodcastId()))
            ->setDuration($distribution->getAttributes()->getDuration())
            ->setPremiumDirectSourceDuration($distribution->getAttributes()->getPremiumDuration());

        $mediaDto->setAuthors($this->transformAuthors($distribution->getTexts()->getAuthors()));
        $mediaDto->setTags($this->transformKeywords($distribution->getTexts()->getKeywords()));
        $mediaDto->setRubric((new ArtemisMediaRubricDto())->setId($distribution->getTexts()->getRubricId()));

        $imagFile = $this->getImagePreview($assetFile->getAsset(), $distribution);
        if ($imagFile) {
            $mediaDto->setImage($this->getImage($imagFile));
        }

        return $mediaDto;
    }

    private function getImagePreview(Asset $asset, ArtemisAudioDistribution $distribution): ?ImageFile
    {
        $episodes = $asset->getEpisodes()->filter(
            fn (PodcastEpisode $episode): bool => $episode->getPodcast()->getId() === $distribution->getTexts()->getPodcastId()
        );

        foreach ($episodes as $episode) {
            $imageFile = null;
            if ($episode->getImagePreview()?->getImageFile()->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Processed)) {
                $imageFile = $episode->getImagePreview()?->getImageFile();
            }
            if ($episode->getPodcast()->getImagePreview()?->getImageFile()->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Processed)) {
                $imageFile = $episode->getPodcast()->getImagePreview()?->getImageFile();
            }

            if ($imageFile) {
                return $imageFile;
            }
        }

        return null;
    }
}
