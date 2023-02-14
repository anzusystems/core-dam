<?php

declare(strict_types=1);

namespace App\Model\Dto\Artemis;

use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class ArtemisAudioDistributionDto
{
    #[Serialize]
    private string $title = '';

    #[Serialize]
    private string $description = '';

    #[Serialize]
    private array $authors = [];

    #[Serialize]
    private array $keywords = [];

    #[Serialize]
    private string $publicUrlLink = '';

    #[Serialize]
    private bool $createArticle = false;

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function setAuthors(array $authors): self
    {
        $this->authors = $authors;

        return $this;
    }

    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function setKeywords(array $keywords): self
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function getPublicUrlLink(): string
    {
        return $this->publicUrlLink;
    }

    public function setPublicUrlLink(string $publicUrlLink): self
    {
        $this->publicUrlLink = $publicUrlLink;

        return $this;
    }

    public function isCreateArticle(): bool
    {
        return $this->createArticle;
    }

    public function setCreateArticle(bool $createArticle): self
    {
        $this->createArticle = $createArticle;

        return $this;
    }
}
