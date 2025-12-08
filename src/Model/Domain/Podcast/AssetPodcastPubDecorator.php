<?php

declare(strict_types=1);

namespace App\Model\Domain\Podcast;

use AnzuSystems\CoreDamBundle\Entity\Podcast;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use AnzuSystems\CoreDamBundle\Entity\PodcastExportData;
use AnzuSystems\CoreDamBundle\Entity\PublicExport;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\App;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;

final class AssetPodcastPubDecorator
{
    private PodcastEpisode $podcastEpisode;
    private PublicExport $publicExport;

    public static function getInstance(
        PodcastEpisode $podcastEpisode,
        PublicExport $publicExport
    ): self {
        return new self()
            ->setPodcastEpisode($podcastEpisode)
            ->setPublicExport($publicExport)
        ;
    }

    public function getPublicExport(): PublicExport
    {
        return $this->publicExport;
    }

    public function setPublicExport(PublicExport $publicExport): self
    {
        $this->publicExport = $publicExport;
        return $this;
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

    #[Serialize]
    public function getExportData(): Collection
    {
        return $this->podcastEpisode->getPodcast()->getExportData()
            ->filter(fn (PodcastExportData $exportData) => $exportData->getExportType()->is($this->getPublicExport()->getType()))
            ->map(fn (PodcastExportData $exportData) => PodcastExportDataPubDecorator::getInstance($exportData))
        ;
    }
}
