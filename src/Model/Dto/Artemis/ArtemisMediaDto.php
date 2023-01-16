<?php

declare(strict_types=1);

namespace App\Model\Dto\Artemis;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Model\Enum\ArtemisMediaType;

class ArtemisMediaDto
{
    #[Serialize]
    protected ?int $id = null;

    #[Serialize]
    protected bool $createArticle = false;

    #[Serialize]
    protected string $externalId = '';

    #[Serialize]
    protected string $title = '';

    #[Serialize]
    protected string $description = '';

    #[Serialize]
    protected int $duration = 0;

    #[Serialize]
    protected ArtemisMediaType $type = ArtemisMediaType::Default;

    #[Serialize]
    protected ?string $youtubeId = null;

    #[Serialize]
    protected ?string $jwId = null;

    #[Serialize(type: ArtemisMediaTagDto::class)]
    protected array $tags = [];

    #[Serialize(type: ArtemisMediaAuthorDto::class)]
    protected array $authors = [];

    #[Serialize]
    protected ArtemisMediaRubricDto $rubric;

    #[Serialize]
    protected ?ArtemisImageDto $image = null;

    public function __construct()
    {
        $this->setRubric(new ArtemisMediaRubricDto());
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId): self
    {
        $this->externalId = $externalId;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
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

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    public function getType(): ArtemisMediaType
    {
        return $this->type;
    }

    public function setType(ArtemisMediaType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getYoutubeId(): ?string
    {
        return $this->youtubeId;
    }

    public function setYoutubeId(?string $youtubeId): self
    {
        $this->youtubeId = $youtubeId;
        return $this;
    }

    public function getJwId(): ?string
    {
        return $this->jwId;
    }

    public function setJwId(?string $jwId): self
    {
        $this->jwId = $jwId;
        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): self
    {
        $this->tags = $tags;
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

    public function getRubric(): ArtemisMediaRubricDto
    {
        return $this->rubric;
    }

    public function setRubric(ArtemisMediaRubricDto $rubric): self
    {
        $this->rubric = $rubric;
        return $this;
    }

    public function getImage(): ?ArtemisImageDto
    {
        return $this->image;
    }

    public function setImage(?ArtemisImageDto $image): self
    {
        $this->image = $image;
        return $this;
    }
}
