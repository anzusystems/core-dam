<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class AssetMetadataBulkChangedEvent extends Event
{
    /**
     * @param array<string> $affectedAssetIds
     */
    public function __construct(
        private readonly array $affectedAssetIds,
    ) {
    }

    /**
     * @return array<string>
     */
    public function getAffectedAssetIds(): array
    {
        return $this->affectedAssetIds;
    }
}
