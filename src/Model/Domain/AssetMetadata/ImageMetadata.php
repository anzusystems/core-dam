<?php

declare(strict_types=1);

namespace App\Model\Domain\AssetMetadata;

use AnzuSystems\SerializerBundle\Attributes\Serialize;

class ImageMetadata
{
    #[Serialize]
    protected string $title = '';

    #[Serialize]
    protected string $description = '';

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
}
