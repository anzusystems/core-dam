<?php

declare(strict_types=1);

namespace App\Model\Fs1Migration;

use App\App;
use DateTimeImmutable;

final readonly class DocumentMigrationDto
{
    public function __construct(
        private int $id,
        private string $fileName,
        private string $filePath,
        private bool $active,
        private string $author,
        private int $createdById,
        private DateTimeImmutable $createdAt,
        private int $cmsSection,
        private string $cmsDomain,
        private string $extension,
    ) {
    }

    public static function createdFromArray(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            fileName: $row['file_name'],
            filePath: $row['file_path'],
            active: 1 === $row['active'],
            author: $row['author'],
            createdById: (int) $row['created_by'],
            createdAt: self::parseDateTimeImmutableFromRow($row, 'created_at'), // todo
            cmsSection: (int) $row['cms_section'],
            cmsDomain: $row['cms_domain'],
            extension: $row['extension'] ?? '',
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getCreatedById(): int
    {
        return $this->createdById;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCmsSection(): int
    {
        return $this->cmsSection;
    }

    public function getCmsDomain(): string
    {
        return $this->cmsDomain;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    private static function parseDateTimeImmutableFromRow(array $row, string $key): DateTimeImmutable
    {
        $defaultDate = (new DateTimeImmutable())->setTimestamp(1_699_697_471);

        if (false === isset($row[$key])) {
            return $defaultDate;
        }

        if ('NULL' === $row[$key]) {
            return $defaultDate;
        }

        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row[$key]);

        if (false === $dateTime) {
            return $defaultDate;
        }

        if ($dateTime < App::getMinDate()) {
            return $defaultDate;
        }

        return $dateTime;
    }
}
