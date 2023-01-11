<?php

declare(strict_types=1);

namespace App\Model\Ugc\Legacy\Embeds;

use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class ImageTextsDto
{
    #[Serialize]
    private string $title = '';

    #[Serialize]
    private string $description = '';

    #[Serialize]
    private string $event = '';

    #[Serialize]
    private array $persons = [];

    public static function getInstance(ImageFile $imageFile): self
    {
        return (new self())
            ->setTitle('')
            ->setDescription($imageFile->getAsset()->getMetadata()->getCustomData()['description'] ?? '')
            ->setEvent('')
            ->setPersons([])
        ;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function setEvent(string $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getPersons(): array
    {
        return $this->persons;
    }

    public function setPersons(array $persons): self
    {
        $this->persons = $persons;

        return $this;
    }
}
