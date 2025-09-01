<?php

declare(strict_types=1);

namespace App\Model\Domain\Asset;

use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\App;
use DateTimeImmutable;
use Symfony\Component\Uid\NilUuid;
use Symfony\Component\Uid\Uuid;

final class AssetCmsSysDto
{
    public const int TITLE_LEGTH = 255;
    public const int DESCRIPTION_LEGTH = 5_000;
    public const int EPISODE_NAME_LENGTH = 255;
    public const int SERIES_NAME_LENGTH = 255;

    #[Serialize]
    private ?Uuid $imageFileId = null;

    #[Serialize]
    private Uuid $assetId;

    #[Serialize]
    private AssetType $assetType = AssetType::Default;

    #[Serialize]
    private string $title = '';

    #[Serialize]
    private string $description = '';

    #[Serialize]
    private string $seriesName = '';

    #[Serialize]
    private string $episodeName = '';

    #[Serialize]
    private ?int $episodeNumber = null;

    #[Serialize]
    private ?DateTimeImmutable $publishedAt = null;

    #[Serialize]
    private int $duration = App::ZERO;

    #[Serialize]
    private ?string $mediaUrl = null;

    #[Serialize]
    private array $authorNames = [];

    #[Serialize]
    private bool $playable = false;

    public function __construct()
    {
        $this->setAssetId(new NilUuid());
    }

    public function getAssetId(): Uuid
    {
        return $this->assetId;
    }

    public function setAssetId(Uuid $assetId): self
    {
        $this->assetId = $assetId;

        return $this;
    }

    public function getImageFileId(): ?Uuid
    {
        return $this->imageFileId;
    }

    public function setImageFileId(?Uuid $imageFileId): self
    {
        $this->imageFileId = $imageFileId;

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

    public function getSeriesName(): string
    {
        return $this->seriesName;
    }

    public function setSeriesName(string $seriesName): self
    {
        $this->seriesName = $seriesName;

        return $this;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

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

    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl;
    }

    public function setMediaUrl(?string $mediaUrl): self
    {
        $this->mediaUrl = $mediaUrl;

        return $this;
    }

    public function isPlayable(): bool
    {
        return $this->playable;
    }

    public function setPlayable(bool $playable): self
    {
        $this->playable = $playable;

        return $this;
    }

    public function getAssetType(): AssetType
    {
        return $this->assetType;
    }

    public function setAssetType(AssetType $assetType): self
    {
        $this->assetType = $assetType;

        return $this;
    }

    public function getAuthorNames(): array
    {
        return $this->authorNames;
    }

    public function setAuthorNames(array $authorNames): self
    {
        $this->authorNames = $authorNames;

        return $this;
    }

    public function getEpisodeName(): string
    {
        return $this->episodeName;
    }

    public function setEpisodeName(string $episodeName): self
    {
        $this->episodeName = $episodeName;

        return $this;
    }

    public function getEpisodeNumber(): ?int
    {
        return $this->episodeNumber;
    }

    public function setEpisodeNumber(?int $episodeNumber): self
    {
        $this->episodeNumber = $episodeNumber;

        return $this;
    }
}
