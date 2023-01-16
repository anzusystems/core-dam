<?php

declare(strict_types=1);

namespace App\Model\Dto\Artemis;

use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class ArtemisRubricDto
{
    #[Serialize]
    private int $id = 0;

    #[Serialize]
    private string $title = '';

    #[Serialize]
    private ?ArtemisSectionDto $section = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function setSection(?ArtemisSectionDto $section): self
    {
        $this->section = $section;

        return $this;
    }

    public function getSection(): ?ArtemisSectionDto
    {
        return $this->section;
    }

    public function getDistributionId(): int
    {
        return $this->getId();
    }
}
