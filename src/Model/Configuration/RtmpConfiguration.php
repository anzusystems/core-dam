<?php

declare(strict_types=1);

namespace App\Model\Configuration;

final class RtmpConfiguration
{
    public const STORAGE_NAME = 'storage_name';
    public const ASSET_LICENCE_ID = 'licence_id';
    public const KEYWORD_ID = 'keyword_id';
    public const TITLE_TEMPLATE = 'title_template';

    public function __construct(
        private readonly string $storageName,
        private readonly int $assetLicenceId,
        private readonly string $keywordId,
        private readonly string $titleTemplate,
    ) {
    }

    public static function getFromArrayConfiguration(array $config): self
    {
        return new self(
            $config[self::STORAGE_NAME] ?? '',
            $config[self::ASSET_LICENCE_ID] ?? 0,
            $config[self::KEYWORD_ID] ?? '',
            $config[self::TITLE_TEMPLATE] ?? '%s',
        );
    }

    public function getStorageName(): string
    {
        return $this->storageName;
    }

    public function getAssetLicenceId(): int
    {
        return $this->assetLicenceId;
    }

    public function getKeywordId(): string
    {
        return $this->keywordId;
    }

    public function getTitleTemplate(): string
    {
        return $this->titleTemplate;
    }
}
