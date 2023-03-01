<?php

declare(strict_types=1);

namespace App\DamMigrations\Cache;

use Doctrine\DBAL\Connection;
use Symfony\Contracts\Service\Attribute\Required;

final class VideoCategoryCache extends AbstractCache
{
    private array $cache = [];

    private Connection $damLegacyConnection;

    #[Required]
    public function setDamLegacyConnection(Connection $damLegacyConnection): void
    {
        $this->damLegacyConnection = $damLegacyConnection;
    }

    public function getCategory(int $legacyCategoryId): ?string
    {
        if (isset($this->cache[$legacyCategoryId])) {
            return $this->cache[$legacyCategoryId];
        }

        $this->cache[$legacyCategoryId] = null;
        $title = $this->damLegacyConnection->fetchOne('SELECT title FROM video_category WHERE id = :id', ['id' => $legacyCategoryId]);
        if (false === $title) {
            return null;
        }

        $id = $this->defaultConnection->fetchOne('SELECT id FROM distribution_category WHERE name = :name', ['name' => $title]);
        if (false === $id) {
            return null;
        }
        $this->cache[$legacyCategoryId] = $id;

        return $id;
    }
}
