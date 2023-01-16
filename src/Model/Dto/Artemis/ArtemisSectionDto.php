<?php

declare(strict_types=1);

namespace App\Model\Dto\Artemis;

use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class ArtemisSectionDto
{
    #[Serialize]
    private int $id = 0;

    #[Serialize]
    private string $title = '';

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
}
