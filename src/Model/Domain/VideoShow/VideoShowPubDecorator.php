<?php

declare(strict_types=1);

namespace App\Model\Domain\VideoShow;

use AnzuSystems\CoreDamBundle\Entity\VideoShow;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;

final class VideoShowPubDecorator
{
    private VideoShow $videoShow;

    public static function getInstance(VideoShow $videoShow): self
    {
        return (new self())
            ->setVideoShow($videoShow)
        ;
    }

    #[Serialize(serializedName: 'id', handler: EntityIdHandler::class)]
    public function getVideoShow(): VideoShow
    {
        return $this->videoShow;
    }

    public function setVideoShow(VideoShow $videoShow): self
    {
        $this->videoShow = $videoShow;
        return $this;
    }

    #[Serialize]
    public function getTitle(): string
    {
        return $this->videoShow->getTexts()->getTitle();
    }
}
