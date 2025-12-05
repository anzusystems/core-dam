<?php

declare(strict_types=1);

namespace App\Model\Domain\Podcast;

use AnzuSystems\CoreDamBundle\Entity\Podcast;
use AnzuSystems\CoreDamBundle\Entity\PodcastExportData;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use Doctrine\Common\Collections\Collection;

final class PodcastCmsSysDecorator
{
    private Podcast $podcast;

    public static function getInstance(
        Podcast $podcast,
    ): self {
        return new self()
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
        return $this->getPodcast()->getTexts()->getTitle();
    }

    #[Serialize]
    public function getDescription(): string
    {
        return $this->getPodcast()->getTexts()->getDescription();
    }

    /**
     * @return Collection<array-key, PodcastExportDataCmsSysDecorator>
     */
    #[Serialize(type: PodcastExportDataCmsSysDecorator::class)]
    public function getExportData(): Collection
    {
        return $this->getPodcast()->getExportData()->map(
            fn (PodcastExportData $exportData) => PodcastExportDataCmsSysDecorator::getInstance($exportData)
        );
    }
}
