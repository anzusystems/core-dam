<?php

declare(strict_types=1);

namespace App\Domain\Asset;

use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\AssetSlot;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Entity\Author;
use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use AnzuSystems\CoreDamBundle\Entity\VideoFile;
use AnzuSystems\CoreDamBundle\Entity\VideoShowEpisode;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use AnzuSystems\CoreDamBundle\Model\Enum\DistributionProcessStatus;
use AnzuSystems\CoreDamBundle\Repository\DistributionRepository;
use App\App;
use App\Configuration\ConfigurationProvider;
use App\Model\Domain\Asset\AssetCmsSysDto;
use Symfony\Component\Uid\Uuid;

final readonly class AssetCmsFactory
{
    public function __construct(
        private DistributionRepository $distributionRepository,
        private ConfigurationProvider $configurationProvider,
        private AudioAssetPubBuilder $audioAssetPubBuilder,
    ) {
    }

    public function create(Asset $asset): AssetCmsSysDto
    {
        $dto = new AssetCmsSysDto();
        if ($asset->getId()) {
            $dto->setAssetId(Uuid::fromString($asset->getId()));
        }
        $customData = $asset->getMetadata()->getCustomData();
        $dto->setTitle(mb_substr($customData['title'] ?? '', App::ZERO, AssetCmsSysDto::TITLE_LEGTH));
        $dto->setDescription(mb_substr($customData['description'] ?? '', App::ZERO, AssetCmsSysDto::DESCRIPTION_LEGTH));
        $dto->setAssetType($asset->getAssetType());
        $dto->setAuthorNames(
            $asset->getAuthors()->map(
                static fn (Author $author): string => $author->getName()
            )->toArray()
        );

        match ($asset->getAssetType()) {
            AssetType::Audio => $this->setAudioProperties($dto, $asset),
            AssetType::Video => $this->setVideoProperties($dto, $asset),
            default => null
        };

        return $dto;
    }

    private function setAudioProperties(AssetCmsSysDto $dto, Asset $asset): void
    {
        $configuration = $this->configurationProvider->getAudioDistribution();

        $firstEpisode = $asset->getEpisodes()->first();
        $firstEpisode = $firstEpisode instanceof PodcastEpisode ? $firstEpisode : null;
        if ($firstEpisode instanceof PodcastEpisode) {
            $podcastId = $firstEpisode->getPodcast()->getId();
            if (is_string($podcastId)) {
                $dto->setSeriesId(Uuid::fromString($podcastId));
            }
            $dto->setSeriesName(mb_substr($firstEpisode->getPodcast()->getTexts()->getTitle(), App::ZERO, AssetCmsSysDto::SERIES_NAME_LENGTH));
            $dto->setEpisodeName(mb_substr($firstEpisode->getTexts()->getTitle(), App::ZERO, AssetCmsSysDto::EPISODE_NAME_LENGTH));
            $dto->setEpisodeNumber($firstEpisode->getAttributes()->getEpisodeNumber());

            $imagePreviewId = $firstEpisode->getPodcast()->getAltImage()?->getImageFile()?->getId() ?? $firstEpisode->getPodcast()->getImagePreview()?->getImageFile()?->getId();
            if ($imagePreviewId) {
                $dto->setImageFileId(Uuid::fromString($imagePreviewId));
            }
            $dto->setPublishedAt($firstEpisode->getDates()->getPublicationDate() ?? $firstEpisode->getCreatedAt());
        }

        $freeSlot = $asset->getSlots()->findFirst(
            static fn (mixed $key, AssetSlot $slot): bool => $slot->getName() === $configuration->getAudioFreeSlotName()
        );
        $audioFile = $freeSlot instanceof AssetSlot ? $freeSlot->getAssetFile() : null;
        if ($audioFile instanceof AudioFile) {
            $dto->setDuration($audioFile->getAttributes()->getDuration());
            $freeMedia = $this->audioAssetPubBuilder->getFreeAudioMedia($freeSlot, $configuration, $firstEpisode);
            $dto->setPlayable(false === empty($freeMedia?->getLinkUrl()));
            $dto->setMediaUrl($freeMedia?->getLinkUrl());
        }
    }

    private function setVideoProperties(AssetCmsSysDto $dto, Asset $asset): void
    {
        $configuration = $this->configurationProvider->getAssetPubConfiguration();
        $firstEpisode = $asset->getVideoEpisodes()->first();
        if ($firstEpisode instanceof VideoShowEpisode) {
            $videoShowId = $firstEpisode->getVideoShow()->getId();
            if (is_string($videoShowId)) {
                $dto->setSeriesId(Uuid::fromString($videoShowId));
            }
            $dto->setSeriesName(mb_substr($firstEpisode->getVideoShow()->getTexts()->getTitle(), App::ZERO, AssetCmsSysDto::SERIES_NAME_LENGTH));
            $dto->setEpisodeName(mb_substr($firstEpisode->getTexts()->getTitle(), App::ZERO, AssetCmsSysDto::EPISODE_NAME_LENGTH));
        }

        $firstSlot = $asset->getSlots()->first();
        $videoFile = $firstSlot instanceof AssetSlot ? $firstSlot->getAssetFile() : null;
        if ($videoFile instanceof VideoFile) {
            $dto->setDuration($videoFile->getAttributes()->getDuration());
            $imagePreviewId = $videoFile->getImagePreview()?->getImageFile()->getId();
            if ($imagePreviewId) {
                $dto->setImageFileId(Uuid::fromString($imagePreviewId));
            }

            $distribution = $this->distributionRepository->findByAsset((string) $asset->getId())
                ->findFirst(
                    static fn (mixed $key, Distribution $distribution): bool => $distribution->getStatus()->is(DistributionProcessStatus::Distributed) &&
                        in_array($distribution->getDistributionService(), $configuration->getVideoAllowedDistributions(), true),
                )
            ;

            if ($distribution instanceof Distribution) {
                $dto->setPlayable(true);
                $dto->setPublishedAt($distribution->getPublishAt() ?? $distribution->getCreatedAt());
            }
        }
    }
}
