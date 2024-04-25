<?php

declare(strict_types=1);

namespace App\Model\Dto\Artemis;

use AnzuSystems\SerializerBundle\Attributes\Serialize;

class ArtemisMediaDto
{
    #[Serialize]
    protected ?int $id = null;

    #[Serialize]
    protected bool $createArticle = false;

    #[Serialize]
    protected bool $bonus = false;

    #[Serialize]
    protected string $externalId = '';

    #[Serialize]
    protected string $title = '';

    #[Serialize]
    protected string $description = '';

    #[Serialize]
    protected ?int $duration = null;

    #[Serialize]
    protected ?int $premiumDirectSourceDuration = null;

    #[Serialize]
    protected ?int $bonusDuration = null;

    #[Serialize]
    protected string $type = '';

    #[Serialize]
    protected string $anzuMediaId = '';

    #[Serialize]
    protected string $assetFileId = '';

    #[Serialize]
    protected string $assetId = '';

    #[Serialize]
    protected string $imagePreviewFileId = '';

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

    public function getAnzuMediaId(): string
    {
        return $this->anzuMediaId;
    }

    public function setAnzuMediaId(string $anzuMediaId): static
    {
        $this->anzuMediaId = $anzuMediaId;

        return $this;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId): static
    {
        $this->externalId = $externalId;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function isCreateArticle(): bool
    {
        return $this->createArticle;
    }

    public function setCreateArticle(bool $createArticle): static
    {
        $this->createArticle = $createArticle;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getYoutubeId(): ?string
    {
        return $this->youtubeId;
    }

    public function setYoutubeId(?string $youtubeId): static
    {
        $this->youtubeId = $youtubeId;

        return $this;
    }

    public function getJwId(): ?string
    {
        return $this->jwId;
    }

    public function setJwId(?string $jwId): static
    {
        $this->jwId = $jwId;

        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function setAuthors(array $authors): static
    {
        $this->authors = $authors;

        return $this;
    }

    public function getRubric(): ArtemisMediaRubricDto
    {
        return $this->rubric;
    }

    public function setRubric(ArtemisMediaRubricDto $rubric): static
    {
        $this->rubric = $rubric;

        return $this;
    }

    public function getImage(): ?ArtemisImageDto
    {
        return $this->image;
    }

    public function setImage(?ArtemisImageDto $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function isBonus(): bool
    {
        return $this->bonus;
    }

    public function setBonus(bool $bonus): static
    {
        $this->bonus = $bonus;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getPremiumDirectSourceDuration(): ?int
    {
        return $this->premiumDirectSourceDuration;
    }

    public function setPremiumDirectSourceDuration(?int $premiumDirectSourceDuration): static
    {
        $this->premiumDirectSourceDuration = $premiumDirectSourceDuration;

        return $this;
    }

    public function getAssetFileId(): string
    {
        return $this->assetFileId;
    }

    public function setAssetFileId(string $assetFileId): static
    {
        $this->assetFileId = $assetFileId;
        return $this;
    }

    public function getAssetId(): string
    {
        return $this->assetId;
    }

    public function setAssetId(string $assetId): static
    {
        $this->assetId = $assetId;
        return $this;
    }

    public function getImagePreviewFileId(): string
    {
        return $this->imagePreviewFileId;
    }

    public function setImagePreviewFileId(string $imagePreviewFileId): static
    {
        $this->imagePreviewFileId = $imagePreviewFileId;
        return $this;
    }

    public function getBonusDuration(): ?int
    {
        return $this->bonusDuration;
    }

    public function setBonusDuration(?int $bonusDuration): static
    {
        $this->bonusDuration = $bonusDuration;
        return $this;
    }
}
