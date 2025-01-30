<?php

declare(strict_types=1);

namespace App\Model\Domain\Podcast;

use AnzuSystems\CoreDamBundle\Entity\Podcast;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\App;
use DateTimeImmutable;

final class AssetPodcastPubDecorator
{
    private PodcastEpisode $podcastEpisode;

    public static function getInstance(PodcastEpisode $podcastEpisode): self
    {
        return (new self())
            ->setPodcastEpisode($podcastEpisode)
        ;
    }

    #[Serialize(handler: EntityIdHandler::class)]
    public function getEpisode(): PodcastEpisode
    {
        return $this->podcastEpisode;
    }

    public function setPodcastEpisode(PodcastEpisode $podcastEpisode): self
    {
        $this->podcastEpisode = $podcastEpisode;
        return $this;
    }

    #[Serialize(serializedName: 'id', handler: EntityIdHandler::class)]
    public function getPodcast(): Podcast
    {
        return $this->podcastEpisode->getPodcast();
    }

    #[Serialize]
    public function getTitle(): string
    {
        return $this->podcastEpisode->getPodcast()->getTexts()->getTitle();
    }

    #[Serialize]
    public function getDescription(): string
    {
        return $this->podcastEpisode->getPodcast()->getTexts()->getDescription();
    }

    #[Serialize]
    public function getEpisodeTitle(): string
    {
        return $this->podcastEpisode->getTexts()->getTitle();
    }

    #[Serialize]
    public function getEpisodeDescription(): string
    {
        return $this->podcastEpisode->getTexts()->getDescription();
    }

    #[Serialize]
    public function getPublicationDate(): DateTimeImmutable
    {
        return $this->podcastEpisode->getDates()->getPublicationDate() ??
            $this->podcastEpisode->getAsset()?->getMainFile()?->getCreatedAt() ??
            App::getAppDate();
    }
}
