<?php

declare(strict_types=1);

namespace App\Model\Csv;

use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class DocumentCsvFile
{
    private const DATETIME_HANDLER = 'Y-m-d H:i:s';

    #[Serialize]
    private int $id;

    #[Serialize(serializedName: 'file_name')]
    private string $fileName;

    #[Serialize(serializedName: 'file_path')]
    private string $filePath;

    #[Serialize(serializedName: 'active')]
    private int $active;

    #[Serialize(serializedName: 'author')]
    private string $author;

    #[Serialize(serializedName: 'created_at')]
    private string $createdAt;

    #[Serialize(serializedName: 'file_size')]
    private int $fileSize;

    #[Serialize(serializedName: 'extension')]
    private string $extension;

    #[Serialize(serializedName: 'central_user_id')]
    private int $createdById;

    #[Serialize(serializedName: 'cms_section_owner_id')]
    private int $cmsSection;

    #[Serialize(serializedName: 'cms_section_owner_domain')]
    private string $cmsDomain = '';

    #[Serialize(serializedName: 'cms_article_url')]
    private string $articleUrl;

    #[Serialize(serializedName: 'cms_article_id_latest')]
    private ?int $articleId;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function getActive(): int
    {
        return $this->active;
    }

    public function setActive(int $active): void
    {
        $this->active = $active;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    public function getCreatedById(): int
    {
        return $this->createdById;
    }

    public function setCreatedById(int $createdById): void
    {
        $this->createdById = $createdById;
    }

    public function getCmsSection(): int
    {
        return $this->cmsSection;
    }

    public function setCmsSection(int $cmsSection): void
    {
        $this->cmsSection = $cmsSection;
    }

    public function getCmsDomain(): string
    {
        return $this->cmsDomain;
    }

    public function setCmsDomain(string $cmsDomain): void
    {
        $this->cmsDomain = $cmsDomain;
    }

    public function getArticleUrl(): string
    {
        return $this->articleUrl;
    }

    public function setArticleUrl(string $articleUrl): void
    {
        $this->articleUrl = $articleUrl;
    }

    public function getArticleId(): ?int
    {
        return $this->articleId;
    }

    public function setArticleId(?int $articleId): void
    {
        $this->articleId = $articleId;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): void
    {
        $this->fileSize = $fileSize;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
