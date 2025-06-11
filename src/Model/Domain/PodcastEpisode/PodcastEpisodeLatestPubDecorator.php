<?php

declare(strict_types=1);

namespace App\Model\Domain\PodcastEpisode;

use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\App;
use App\Model\Domain\Asset\AssetPodcastEpisodePubDecorator;
use App\Model\Domain\Asset\AudioAssetMediaPubDecorator;
use App\Model\Domain\Asset\AudioImageThumbnailPubDecorator;
use App\Model\Domain\Podcast\PodcastTitlePubDecorator;
use DateTimeImmutable;

final class PodcastEpisodeLatestPubDecorator
{
    private PodcastEpisode $podcastEpisode;
    private ?AudioAssetMediaPubDecorator $bonusAudioMedia = null;

    public static function getInstance(
        PodcastEpisode $podcastEpisode,
        ?AudioAssetMediaPubDecorator $assetBonusAudioMedia = null
    ): self {
        return (new self())
            ->setPodcastEpisode($podcastEpisode)
            ->setBonusAudioMedia($assetBonusAudioMedia)
        ;
    }

    #[Serialize(serializedName: 'id', handler: EntityIdHandler::class)]
    public function getPodcastEpisode(): PodcastEpisode
    {
        return $this->podcastEpisode;
    }

    public function setPodcastEpisode(PodcastEpisode $podcastEpisode): self
    {
        $this->podcastEpisode = $podcastEpisode;
        return $this;
    }

    public function getBonusAudioMedia(): ?AudioAssetMediaPubDecorator
    {
        return $this->bonusAudioMedia;
    }

    public function setBonusAudioMedia(?AudioAssetMediaPubDecorator $bonusAudioMedia): self
    {
        $this->bonusAudioMedia = $bonusAudioMedia;

        return $this;
    }

    #[Serialize]
    public function getPodcast(): PodcastTitlePubDecorator
    {
        return PodcastTitlePubDecorator::getInstance($this->podcastEpisode->getPodcast());
    }

    #[Serialize]
    public function getTitle(): string
    {
        return $this->podcastEpisode->getTexts()->getTitle();
    }

    #[Serialize]
    public function getDescription(): string
    {
        return $this->podcastEpisode->getTexts()->getDescription();
    }

    #[Serialize]
    public function getDuration(): int
    {
        return $this->podcastEpisode->getAttributes()->getDuration();
    }

    #[Serialize]
    public function getPublicationDate(): DateTimeImmutable
    {
        return $this->podcastEpisode->getDates()->getPublicationDate() ??
            $this->podcastEpisode->getAsset()?->getMainFile()?->getCreatedAt() ??
            App::getAppDate();
    }

    #[Serialize]
    public function getAsset(): ?AssetPodcastEpisodePubDecorator
    {
        return $this->podcastEpisode->getAsset()
            ? AssetPodcastEpisodePubDecorator::getInstance(
                asset: $this->podcastEpisode->getAsset(),
                media: [$this->getBonusAudioMedia()]
            )
            : null
        ;
    }

    #[Serialize]
    public function getThumbnail(): AudioImageThumbnailPubDecorator
    {
        return AudioImageThumbnailPubDecorator::getInstance(
            $this->podcastEpisode->getImagePreview()?->getImageFile() ??
            $this->podcastEpisode->getPodcast()->getImagePreview()?->getImageFile()
        );
    }
}
