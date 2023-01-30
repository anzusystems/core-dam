<?php

declare(strict_types=1);

namespace App\DamMigrations\Cache;

use Doctrine\DBAL\Connection;

abstract class AbstractCache
{
    public function __construct(
        protected readonly Connection $defaultConnection
    ) {
    }
}
