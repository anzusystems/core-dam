<?php

declare(strict_types=1);

namespace App\Model;

final readonly class MigrateConfig
{
    public function __construct(
        private bool $ugc,
    ) {
    }

    public function isUgc(): bool
    {
        return $this->ugc;
    }

    public function isNotUgc(): bool
    {
        return false === $this->isUgc();
    }


}
