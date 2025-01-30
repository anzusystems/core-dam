<?php

declare(strict_types=1);

namespace App\Model\Configuration;

final class AssetPubConfiguration
{
    public const string VIDEO_ALLOWED_DISTRIBUTIONS = 'video_allowed_distributions';
    public const string METADATA_TITLE = 'metadata_title';

    public function __construct(
        private readonly array $videoAllowedDistributions,
        private readonly string $metadataTitle
    ) {
    }

    public static function getFromArrayConfiguration(array $config): self
    {
        return new self(
            $config[self::VIDEO_ALLOWED_DISTRIBUTIONS] ?? [],
            $config[self::METADATA_TITLE] ?? '',
        );
    }

    /**
     * @return string[]
     */
    public function getVideoAllowedDistributions(): array
    {
        return $this->videoAllowedDistributions;
    }

    public function getMetadataTitle(): string
    {
        return $this->metadataTitle;
    }
}
