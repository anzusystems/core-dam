<?php

declare(strict_types=1);

namespace App\Model;

final readonly class MediaApiMigrationIteratorConfig
{
    public function __construct(
        private ?int $fromId,
        private ?int $toId,
    ) {
    }

    public function getFromId(): ?int
    {
        return $this->fromId;
    }

    public function getToId(): ?int
    {
        return $this->toId;
    }
}
