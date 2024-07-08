<?php

declare(strict_types=1);

namespace App\Model\Csv;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\ArrayStringHandler;
use AnzuSystems\SerializerBundle\Handler\Handlers\DateTimeHandler;
use DateTimeImmutable;

final class Fs1CsvFile
{
    private const string DATETIME_HANDLER = 'Y-m-d H:i:s';

    #[Serialize]
    private int $id;

    #[Serialize]
    private string $title;

    #[Serialize]
    private string $perex;

    #[Serialize(serializedName: 'section_short_name')]
    private string $sectionShortName;

    #[Serialize(serializedName: 'rubric_id')]
    private int $rubricId;

    #[Serialize(serializedName: 'rubric_name')]
    private string $rubricName;

    #[Serialize(serializedName: 'series_id')]
    private int $seriesId;

    #[Serialize(serializedName: 'series_name')]
    private string $seriesName;

    #[Serialize(handler: ArrayStringHandler::class)]
    private array $author;

    #[Serialize(handler: ArrayStringHandler::class)]
    private array $keywords;

    #[Serialize(serializedName: 'central_user_id')]
    private int $createdBy;

    #[Serialize(serializedName: 'updated_at', handler: DateTimeHandler::class, type: self::DATETIME_HANDLER)]
    private DateTimeImmutable $modifiedAt;

    #[Serialize(serializedName: 'created_at', handler: DateTimeHandler::class, type: self::DATETIME_HANDLER)]
    private DateTimeImmutable $createdAt;

    #[Serialize(serializedName: 'published_at', handler: DateTimeHandler::class, type: self::DATETIME_HANDLER)]
    private DateTimeImmutable $publishedAt;

    #[Serialize('file_path')]
    private string $filePath;

    #[Serialize('file_name')]
    private string $fileName;

    #[Serialize('file_name_hd_quality')]
    private string $fileNameHdQuality;

    #[Serialize('image_file_name')]
    private string $imageFileName;

    #[Serialize('youtube_code')]
    private string $youtubeCode;

    #[Serialize('cms_admin_url')]
    private string $cmsAdminUrl;

    #[Serialize('cms_article_url')]
    private string $cmsArticleUrl;

    #[Serialize('fs1_available')]
    private int $available;

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

    public function getPerex(): string
    {
        return $this->perex;
    }

    public function setPerex(string $perex): self
    {
        $this->perex = $perex;
        return $this;
    }

    public function getSectionShortName(): string
    {
        return $this->sectionShortName;
    }

    public function setSectionShortName(string $sectionShortName): self
    {
        $this->sectionShortName = $sectionShortName;
        return $this;
    }

    public function getRubricId(): int
    {
        return $this->rubricId;
    }

    public function setRubricId(int $rubricId): self
    {
        $this->rubricId = $rubricId;
        return $this;
    }

    public function getRubricName(): string
    {
        return $this->rubricName;
    }

    public function setRubricName(string $rubricName): self
    {
        $this->rubricName = $rubricName;
        return $this;
    }

    public function getSeriesId(): int
    {
        return $this->seriesId;
    }

    public function setSeriesId(int $seriesId): self
    {
        $this->seriesId = $seriesId;
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

    public function getAuthor(): array
    {
        return $this->author;
    }

    public function setAuthor(array $author): self
    {
        $this->author = $author;
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

    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    public function setCreatedBy(int $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getModifiedAt(): DateTimeImmutable
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(DateTimeImmutable $modifiedAt): self
    {
        $this->modifiedAt = $modifiedAt;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getPublishedAt(): DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;
        return $this;
    }

    public function getFileNameHdQuality(): string
    {
        return $this->fileNameHdQuality;
    }

    public function setFileNameHdQuality(string $fileNameHdQuality): self
    {
        $this->fileNameHdQuality = $fileNameHdQuality;
        return $this;
    }

    public function getImageFileName(): string
    {
        return $this->imageFileName;
    }

    public function setImageFileName(string $imageFileName): self
    {
        $this->imageFileName = $imageFileName;
        return $this;
    }

    public function getYoutubeCode(): string
    {
        return $this->youtubeCode;
    }

    public function setYoutubeCode(string $youtubeCode): self
    {
        $this->youtubeCode = $youtubeCode;
        return $this;
    }

    public function getCmsAdminUrl(): string
    {
        return $this->cmsAdminUrl;
    }

    public function setCmsAdminUrl(string $cmsAdminUrl): self
    {
        $this->cmsAdminUrl = $cmsAdminUrl;
        return $this;
    }

    public function getCmsArticleUrl(): string
    {
        return $this->cmsArticleUrl;
    }

    public function setCmsArticleUrl(string $cmsArticleUrl): self
    {
        $this->cmsArticleUrl = $cmsArticleUrl;
        return $this;
    }

    public function getAvailable(): int
    {
        return $this->available;
    }

    public function setAvailable(int $available): self
    {
        $this->available = $available;
        return $this;
    }
}
