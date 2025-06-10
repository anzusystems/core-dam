<?php

declare(strict_types=1);

namespace App\Model\Domain\Podcast;

use AnzuSystems\CoreDamBundle\Entity\Podcast;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;

final class PodcastTitlePubDecorator
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
}
