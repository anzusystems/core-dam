<?php

declare(strict_types=1);

namespace App\Model\MediaApiMigration;

use DateTimeImmutable;

final readonly class ImageMigrationDto
{
    public function __construct(
        private int $mediaApiId,
        private string $mainFileId,
        private string $sourceUrl,
        private string $description,
        private ?string $authorId,
        private array $keywords,
        private ?DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $updatedAt,
        private ?int $focusX,
        private ?int $focusY,
        private int $updatedById,
        private int $createdById,
        private string $filePath,
        private string $migrationStatus,
        private string $failReason,
        private string $outputLog,
    ) {
    }

    public static function createdFromArray(array $row): self
    {
        // todo datetimezone?
        return new self(
            mediaApiId: $row['media_api_id'],
            mainFileId: $row['main_file_id'],
            sourceUrl: $row['source_url'],
            description: $row['description'],
            authorId: $row['author_id'],
            keywords: json_decode($row['keywords'] ?? '[]'),
            createdAt: self::parseDateTimeImmutableFromRow($row, 'created_at'),
            updatedAt: self::parseDateTimeImmutableFromRow($row, 'updated_at'),
            focusX: $row['focus_x'],
            focusY: $row['focus_y'],
            updatedById: $row['updated_by'],
            createdById: $row['created_by'],
            filePath: $row['file_path'],
            migrationStatus: $row['migration_status'],
            failReason: $row['fail_reason'],
            outputLog: $row['output_log'],
        );
    }

    public function getMediaApiId(): int
    {
        return $this->mediaApiId;
    }

    public function getMainFileId(): string
    {
        return $this->mainFileId;
    }

    public function getSourceUrl(): string
    {
        return $this->sourceUrl;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getAuthorId(): ?string
    {
        return $this->authorId;
    }

    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getFocusX(): ?int
    {
        return $this->focusX;
    }

    public function getFocusY(): ?int
    {
        return $this->focusY;
    }

    public function getUpdatedById(): int
    {
        return $this->updatedById;
    }

    public function getCreatedById(): int
    {
        return $this->createdById;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getMigrationStatus(): string
    {
        return $this->migrationStatus;
    }

    public function getFailReason(): string
    {
        return $this->failReason;
    }

    public function getOutputLog(): string
    {
        return $this->outputLog;
    }

    private static function parseDateTimeImmutableFromRow(array $row, string $key): ?DateTimeImmutable
    {
        if (false === isset($row[$key])) {
            return null;
        }

        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row[$key]);
        if ($dateTime instanceof DateTimeImmutable) {
            return $dateTime;
        }

        return null;
    }
}
