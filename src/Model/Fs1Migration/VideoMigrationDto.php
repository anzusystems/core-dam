<?php

declare(strict_types=1);

namespace App\Model\Fs1Migration;

use App\App;
use DateTimeImmutable;

final readonly class VideoMigrationDto
{
    public function __construct(
        private int $mediaId,
        private string $assetId,
        private string $title,
        private DateTimeImmutable $pubDate,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $modifiedAt,
        private int $rubricId,
        private int $createdById,
        private string $rubricTitle,
        private int $showId,
        private string $showTitle,
        private array $authors,
        private array $keywords,
        private string $youtubeCode,
        private string $jwCode,
        private string $mediaUrl,
        private string $articleUrl,
        private string $dir,
        private string $filePath,
        private string $filePathHd,
        private string $filePathImage,
        private array $authorIds,
        private array $keywordIds,
        private string $showUuid,
        private string $episodeUuid,
        private string $categoryUuid,
    ) {
    }

    public static function createdFromArray(array $row): self
    {
        return new self(
            mediaId: $row['media_id'],
            assetId: $row['asset_id'],
            title: $row['title'],
            pubDate: self::parseDateTimeImmutableFromRow($row, 'pub_date'),
            createdAt: self::parseDateTimeImmutableFromRow($row, 'created_at'),
            modifiedAt: self::parseDateTimeImmutableFromRow($row, 'modified_at'),
            rubricId: $row['rubric_id'],
            createdById: $row['created_by_id'],
            rubricTitle: $row['rubric_title'],
            showId: $row['show_id'],
            showTitle: $row['show_title'],
            authors: json_decode($row['authors'] ?? '[]'),
            keywords: json_decode($row['keywords'] ?? '[]'),
            youtubeCode: $row['yt_id'],
            jwCode: $row['jw_id'],
            mediaUrl: $row['media_url'],
            articleUrl: $row['article_url'],
            dir: $row['dir'],
            filePath: $row['file_path'],
            filePathHd: $row['file_path_hd'],
            filePathImage: $row['file_path_image'],
            authorIds: json_decode($row['author_ids'] ?? '[]'),
            keywordIds: json_decode($row['keyword_ids'] ?? '[]'),
            showUuid: $row['show_uuid'],
            episodeUuid: $row['episode_uuid'],
            categoryUuid: $row['category_uuid']
        );
    }

    public function getMediaId(): int
    {
        return $this->mediaId;
    }

    public function getAssetId(): string
    {
        return $this->assetId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPubDate(): DateTimeImmutable
    {
        return $this->pubDate;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getModifiedAt(): DateTimeImmutable
    {
        return $this->modifiedAt;
    }

    public function getRubricId(): int
    {
        return $this->rubricId;
    }

    public function getCreatedById(): int
    {
        return $this->createdById;
    }

    public function getRubricTitle(): string
    {
        return $this->rubricTitle;
    }

    public function getShowId(): int
    {
        return $this->showId;
    }

    public function getShowTitle(): string
    {
        return $this->showTitle;
    }

    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function getYoutubeCode(): string
    {
        return $this->youtubeCode;
    }

    public function getMediaUrl(): string
    {
        return $this->mediaUrl;
    }

    public function getArticleUrl(): string
    {
        return $this->articleUrl;
    }

    public function getDir(): string
    {
        return $this->dir;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getFilePathHd(): string
    {
        return $this->filePathHd;
    }

    public function getFilePathImage(): string
    {
        return $this->filePathImage;
    }

    /**
     * @return string[]
     */
    public function getAuthorIds(): array
    {
        return $this->authorIds;
    }

    /**
     * @return string[]
     */
    public function getKeywordIds(): array
    {
        return $this->keywordIds;
    }

    public function getShowUuid(): string
    {
        return $this->showUuid;
    }

    public function getEpisodeUuid(): string
    {
        return $this->episodeUuid;
    }

    public function getCategoryUuid(): string
    {
        return $this->categoryUuid;
    }

    public function getJwCode(): string
    {
        return $this->jwCode;
    }

    private static function parseDateTimeImmutableFromRow(array $row, string $key): DateTimeImmutable
    {
        if (false === isset($row[$key])) {
            return App::getMinDate();
        }

        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row[$key]);

        if (false === $dateTime) {
            return App::getMinDate();
        }

        if ($dateTime < App::getMinDate()) {
            return App::getMinDate();
        }

        return $dateTime;
    }
}
