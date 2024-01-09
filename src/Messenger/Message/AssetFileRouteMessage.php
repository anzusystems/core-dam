<?php

declare(strict_types=1);

namespace App\Messenger\Message;

final readonly class AssetFileRouteMessage
{
    public function __construct(
        protected string $assetFileId,
        protected string $fullUrl,
    ) {
    }

    public function getAssetFileId(): string
    {
        return $this->assetFileId;
    }

    public function getFullUrl(): string
    {
        return $this->fullUrl;
    }
}
