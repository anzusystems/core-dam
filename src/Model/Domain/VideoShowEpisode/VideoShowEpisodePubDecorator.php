<?php

declare(strict_types=1);

namespace App\Model\Domain\VideoShowEpisode;

use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\VideoFile;
use AnzuSystems\CoreDamBundle\Entity\VideoShowEpisode;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\Model\Domain\Asset\VideoImageThumbnailPubDecorator;
use DateTimeImmutable;

final class VideoShowEpisodePubDecorator
{
    private VideoShowEpisode $videoShowEpisode;
    public static function getInstance(VideoShowEpisode $videoShowEpisode): self
    {
        return (new self())
            ->setVideoShowEpisode($videoShowEpisode)
        ;
    }

    #[Serialize(serializedName: 'id', handler: EntityIdHandler::class)]
    public function getVideoShowEpisode(): VideoShowEpisode
    {
        return $this->videoShowEpisode;
    }

    public function setVideoShowEpisode(VideoShowEpisode $videoShowEpisode): self
    {
        $this->videoShowEpisode = $videoShowEpisode;
        return $this;
    }

    #[Serialize]
    public function getTitle(): string
    {
        return $this->videoShowEpisode->getTexts()->getTitle();
    }

    #[Serialize(handler: EntityIdHandler::class)]
    public function getAsset(): ?Asset
    {
        return $this->videoShowEpisode->getAsset();
    }

    #[Serialize]
    public function getThumbnail(): VideoImageThumbnailPubDecorator
    {
        /** @var VideoFile|null $videoFile */
        $videoFile = $this->videoShowEpisode->getAsset()?->getMainFile();

        return VideoImageThumbnailPubDecorator::getInstance($videoFile?->getImagePreview()?->getImageFile());
    }

    #[Serialize]
    public function getDuration(): int
    {
        $videoFile = $this->videoShowEpisode->getAsset()?->getMainFile();

        return $videoFile instanceof VideoFile ? $videoFile->getAttributes()->getDuration() : 0;
    }

    #[Serialize]
    public function getPublicationDate(): DateTimeImmutable
    {
        return $this->videoShowEpisode->getDates()->getPublicationDate();
    }
}
