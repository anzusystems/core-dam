<?php

declare(strict_types=1);

namespace App\Model\Domain\Podcast;

use AnzuSystems\CoreDamBundle\Entity\Podcast;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\Model\Domain\Asset\AudioImageThumbnailPubDecorator;

final class PodcastPubDecorator
{
    private Podcast $podcast;

    public static function getInstance(Podcast $podcast): self
    {
        return (new self())
            ->setPodcast($podcast)
        ;
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
}
