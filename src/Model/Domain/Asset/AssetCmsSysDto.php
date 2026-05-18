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
    private ?Uuid $seriesId = null;

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

    // TTS-specific optional fields (populated only for TTS-generated audio assets).

    #[Serialize]
    private bool $generated = false;

    #[Serialize]
    private ?string $voiceFamilySlug = null;

    #[Serialize]
    private ?string $extResourceName = null;

    #[Serialize]
    private ?string $extId = null;

    #[Serialize]
    private ?string $extVersion = null;

    #[Serialize]
    private bool $includeInRecommendedPodcast = false;

    #[Serialize]
    private ?DateTimeImmutable $lastRegeneratedAt = null;

    #[Serialize]
    private ?string $previewMediaUrl = null;

    #[Serialize]
    private ?int $licenceId = null;

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

    public function getSeriesId(): ?Uuid
    {
        return $this->seriesId;
    }

    public function setSeriesId(?Uuid $seriesId): self
    {
        $this->seriesId = $seriesId;

        return $this;
    }

    public function isGenerated(): bool
    {
        return $this->generated;
    }

    public function setGenerated(bool $generated): self
    {
        $this->generated = $generated;

        return $this;
    }

    public function getVoiceFamilySlug(): ?string
    {
        return $this->voiceFamilySlug;
    }

    public function setVoiceFamilySlug(?string $voiceFamilySlug): self
    {
        $this->voiceFamilySlug = $voiceFamilySlug;

        return $this;
    }

    public function getExtResourceName(): ?string
    {
        return $this->extResourceName;
    }

    public function setExtResourceName(?string $extResourceName): self
    {
        $this->extResourceName = $extResourceName;

        return $this;
    }

    public function getExtId(): ?string
    {
        return $this->extId;
    }

    public function setExtId(?string $extId): self
    {
        $this->extId = $extId;

        return $this;
    }

    public function getExtVersion(): ?string
    {
        return $this->extVersion;
    }

    public function setExtVersion(?string $extVersion): self
    {
        $this->extVersion = $extVersion;

        return $this;
    }

    public function isIncludeInRecommendedPodcast(): bool
    {
        return $this->includeInRecommendedPodcast;
    }

    public function setIncludeInRecommendedPodcast(bool $includeInRecommendedPodcast): self
    {
        $this->includeInRecommendedPodcast = $includeInRecommendedPodcast;

        return $this;
    }

    public function getLastRegeneratedAt(): ?DateTimeImmutable
    {
        return $this->lastRegeneratedAt;
    }

    public function setLastRegeneratedAt(?DateTimeImmutable $lastRegeneratedAt): self
    {
        $this->lastRegeneratedAt = $lastRegeneratedAt;

        return $this;
    }

    public function getPreviewMediaUrl(): ?string
    {
        return $this->previewMediaUrl;
    }

    public function setPreviewMediaUrl(?string $previewMediaUrl): self
    {
        $this->previewMediaUrl = $previewMediaUrl;

        return $this;
    }

    public function getLicenceId(): ?int
    {
        return $this->licenceId;
    }

    public function setLicenceId(?int $licenceId): self
    {
        $this->licenceId = $licenceId;

        return $this;
    }
}
