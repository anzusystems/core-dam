<?php

declare(strict_types=1);

namespace App\Model\Domain\Podcast;

use AnzuSystems\CoreDamBundle\Entity\Podcast;
use AnzuSystems\CoreDamBundle\Entity\PodcastExportData;
use AnzuSystems\CoreDamBundle\Entity\PublicExport;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\Model\Domain\Asset\AudioImageThumbnailPubDecorator;
use Doctrine\Common\Collections\Collection;

final class PodcastPubDecorator
{
    private Podcast $podcast;
    private PublicExport $publicExport;

    public static function getInstance(
        Podcast $podcast,
        PublicExport $publicExport
    ): self {
        return new self()
            ->setPodcast($podcast)
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

    #[Serialize(serializedName: 'id', handler: EntityIdHandler::class)]
    public function getPodcast(): Podcast
    {
        return $this->podcast;
    }

    public function setPodcast(Podcast $podcast): self
    {
        $this->podcast = $podcast;
        return $this;
    }

    #[Serialize]
    public function getTitle(): string
    {
        return $this->podcast->getTexts()->getTitle();
    }

    #[Serialize]
    public function getDescription(): string
    {
        return $this->podcast->getTexts()->getDescription();
    }

    #[Serialize]
    public function getRssUrl(): string
    {
        return $this->podcast->getAttributes()->getRssUrl();
    }

    #[Serialize]
    public function getThumbnail(): AudioImageThumbnailPubDecorator
    {
        return AudioImageThumbnailPubDecorator::getInstance(
            $this->podcast->getImagePreview()?->getImageFile()
        );
    }

    #[Serialize]
    public function getExportData(): Collection
    {
        return $this->podcast->getExportData()
            ->filter(fn (PodcastExportData $exportData) => $exportData->getExportType()->is($this->getPublicExport()->getType()))
            ->map(fn (PodcastExportData $exportData) => PodcastExportDataPubDecorator::getInstance($exportData))
        ;
    }
}
