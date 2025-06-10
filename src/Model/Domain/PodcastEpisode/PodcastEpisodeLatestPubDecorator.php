<?php

declare(strict_types=1);

namespace App\Model\Domain\PodcastEpisode;

use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\App;
use App\Model\Domain\Asset\AudioImageThumbnailPubDecorator;
use App\Model\Domain\Podcast\PodcastTitlePubDecorator;
use DateTimeImmutable;

final class PodcastEpisodeLatestPubDecorator
{
    private PodcastEpisode $podcastEpisode;

    public static function getInstance(PodcastEpisode $podcastEpisode): self
    {
        return (new self())
            ->setPodcastEpisode($podcastEpisode)
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

    #[Serialize(handler: EntityIdHandler::class)]
    public function getAsset(): ?Asset
    {
        return $this->podcastEpisode->getAsset();
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
