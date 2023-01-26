<?php

declare(strict_types=1);


namespace App\DamMigrations\Cache;

final class PodcastCache extends AbstractCache
{
    private array $cache = [];

    public function getPodcast(string $title): string
    {
        // todo getOrCreate
        if (isset($this->cache[$title])) {
            return $this->cache[$title];
        }

        $id = $this->defaultConnection->fetchOne('SELECT id FROM podcast WHERE texts_title = :title', ['title' => $title]);
        $this->cache[$title] = (string) $id;

        return $id;
    }
}