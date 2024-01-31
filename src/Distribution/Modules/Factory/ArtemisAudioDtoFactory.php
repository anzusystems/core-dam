<?php

declare(strict_types=1);

namespace App\Distribution\Modules\Factory;

use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Entity\JwDistribution;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\CoreDamBundle\Repository\DistributionRepository;
use App\App;
use App\Entity\ArtemisAudioDistribution;
use App\Model\Dto\Artemis\ArtemisAudioMediaDto;
use App\Model\Dto\Artemis\ArtemisMediaChannel;
use App\Model\Dto\Artemis\ArtemisMediaRubricDto;
use App\Model\Enum\ArtemisMediaType;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;

final class ArtemisAudioDtoFactory extends AbstractArtemisDtoFactory
{
    public function __construct(
        private readonly DistributionRepository $repository,
    ) {
    }

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
            ->setAnzuPodcastEpisodeId($distribution->getTexts()->getEpisodeId())
            ->setDuration($distribution->getAttributes()->getDuration())
            ->setPremiumDirectSourceDuration($distribution->getAttributes()->getPremiumDuration())
            ->setType(ArtemisMediaType::Audio->toString());

        if ($distribution->getPublishAt()) {
            $mediaDto->setPublishedAt(
                DateTimeImmutable::createFromMutable(
                    DateTime::createFromImmutable($distribution->getPublishAt())->setTimezone(new DateTimeZone(App::DATE_TIME_ZONE))
                )
            );
        }

        $mediaDto->setAuthors($this->transformAuthors($distribution->getTexts()->getAuthors()));
        $mediaDto->setTags($this->transformKeywords($distribution->getTexts()->getKeywords()));
        $mediaDto->setRubric((new ArtemisMediaRubricDto())->setId($distribution->getTexts()->getRubricId()));

        $imagFile = $this->getImagePreview($assetFile->getAsset(), $distribution);
        if ($imagFile) {
            $mediaDto->setImage($this->getImage($imagFile));
        }

        $distributions = $this->repository->findByAssetFile((string) $assetFile->getId());
        foreach ($distributions as $distribution) {
            if ($distribution instanceof JwDistribution && false === empty($distribution->getExtId())) {
                $mediaDto->setJwId($distribution->getExtId());
            }
        }

        return $mediaDto;
    }

    public function getImagePreview(Asset $asset, ArtemisAudioDistribution $distribution): ?ImageFile
    {
        $episodes = $asset->getEpisodes()->filter(
            fn (PodcastEpisode $episode): bool => $episode->getPodcast()->getId() === $distribution->getTexts()->getPodcastId()
        );

        foreach ($episodes as $episode) {
            $imageFile = $this->getEpisodeImage($episode);

            if ($imageFile) {
                return $imageFile;
            }
        }

        return null;
    }

    private function getEpisodeImage(PodcastEpisode $episode): ?ImageFile
    {
        if ($episode->getImagePreview()?->getImageFile()->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Processed)) {
            return $episode->getImagePreview()?->getImageFile();
        }
        if ($episode->getPodcast()->getImagePreview()?->getImageFile()->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Processed)) {
            return $episode->getPodcast()->getImagePreview()?->getImageFile();
        }

        return null;
    }
}
