<?php

declare(strict_types=1);

namespace App\Messenger\Message;

final readonly class AssetChangedMessage
{
    /**
     * @param array<string> $assetIds
     */
    public function __construct(
        private array $assetIds,
    ) {
    }

    /**
     * @return array<string>
     */
    public function getAssetIds(): array
    {
        return $this->assetIds;
    }
}
