<?php

declare(strict_types=1);


namespace App\DamMigrations\Cache;

final class KeywordCache extends AbstractCache
{
    private array $cache = [];

    public function getKeyword(string $title): string
    {
        // todo getOrCreate
        if (isset($this->cache[$title])) {
            return $this->cache[$title];
        }

        $id = $this->defaultConnection->fetchOne('SELECT id FROM keyword WHERE name = :title', ['title' => $title]);
        $this->cache[$title] = (string) $id;

        return $id;
    }
}