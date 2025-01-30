<?php

declare(strict_types=1);

namespace App\Model\Domain\VideoShowEpisode;

use AnzuSystems\CoreDamBundle\Entity\VideoShow;
use AnzuSystems\CoreDamBundle\Entity\VideoShowEpisode;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use DateTimeImmutable;

final class AssetVideoShowEpisodePubDecorator
{
    private VideoShowEpisode $videoShowEpisode;
    public static function getInstance(VideoShowEpisode $videoShowEpisode): self
    {
        return (new self())
            ->setVideoShowEpisode($videoShowEpisode)
        ;
    }

    #[Serialize(handler: EntityIdHandler::class)]
    public function getEpisode(): VideoShowEpisode
    {
        return $this->videoShowEpisode;
    }

    public function setVideoShowEpisode(VideoShowEpisode $videoShowEpisode): self
    {
        $this->videoShowEpisode = $videoShowEpisode;
        return $this;
    }

    #[Serialize(serializedName: 'id', handler: EntityIdHandler::class)]
    public function getVideoShow(): VideoShow
    {
        return $this->videoShowEpisode->getVideoShow();
    }

    #[Serialize]
    public function getTitle(): string
    {
        return $this->videoShowEpisode->getVideoShow()->getTexts()->getTitle();
    }

    #[Serialize]
    public function getEpisodeTitle(): string
    {
        return $this->videoShowEpisode->getTexts()->getTitle();
    }

    #[Serialize]
    public function getPublicationDate(): DateTimeImmutable
    {
        return $this->videoShowEpisode->getDates()->getPublicationDate();
    }
}
