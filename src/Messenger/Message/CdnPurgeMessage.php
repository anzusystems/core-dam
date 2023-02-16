<?php

declare(strict_types=1);

namespace App\Messenger\Message;

final readonly class CdnPurgeMessage
{
    public function __construct(
        private array $paths
    ) {
    }

    public function getPaths(): array
    {
        return $this->paths;
    }
}
