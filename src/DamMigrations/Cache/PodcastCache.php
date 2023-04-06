<?php

declare(strict_types=1);

namespace App\DamMigrations\Cache;

final class PodcastCache extends AbstractCache
{
    private const DAM_TO_ARTEMIS_MAP = [
        11 => '1edc17e7-d814-6eb0-85cf-3126b6164b0b',
        26 => '1edc17e7-dca7-6022-bf8d-3126b6164b0b',
        19 => '1edc17e7-dae2-6e58-bd85-3126b6164b0b',
        29 => '1edc17e7-dd32-6c6c-9d5b-3126b6164b0b',
        36 => '1edc17e7-e5f3-62c0-87a1-3126b6164b0b',
        34 => '1edc17e7-e5f3-62c0-87a1-3126b6164b0b',
        33 => '1edc17e7-de13-6366-a8e7-3126b6164b0b',
        32 => '1edc17e7-ddc6-64bc-9ce7-3126b6164b0b',
        17 => '1edc17e7-da42-6aca-8fdb-3126b6164b0b',
        13 => '1edc17e7-d8b4-68b6-bebd-3126b6164b0b',
        25 => '1edc17e7-dc6e-6d80-83b8-3126b6164b0b',
        30 => '1edc17e7-dd6f-6bbc-8879-3126b6164b0b',
        21 => '1edc17e7-db58-686a-b43f-3126b6164b0b',
        9 => '1edc17e7-d79c-6eb0-bc1e-3126b6164b0b',
        28 => '1edc17e7-dd05-6474-a958-3126b6164b0b',
        10 => '1edc17e7-d7cd-6f56-9780-3126b6164b0b',
        12 => '1edc17e7-d875-6d82-a4a0-3126b6164b0b',
        15 => '1edc17e7-d9a9-64c4-bf1c-3126b6164b0b',
        35 => '1edc17e7-dcd4-68ce-90b4-3126b6164b0b',
        27 => '1edc17e7-de44-6038-90e2-3126b6164b0b',
        22 => '1edc17e7-db96-6ab6-ae7c-3126b6164b0b',
        14 => '1edc17e7-d94b-6342-8be6-3126b6164b0b',
        23 => '1edc17e7-dbe6-6b38-a952-3126b6164b0b',
        31 => '1edc17e7-dd96-63d4-83b8-3126b6164b0b',
        16 => '1edc17e7-d9f7-6d4a-9f39-3126b6164b0b',
        3 => '1edc17e7-d734-6b3a-8bee-3126b6164b0b',
        20 => '1edc17e7-db15-63c6-8796-3126b6164b0b',
        18 => '1edc17e7-da89-681c-bb8e-3126b6164b0b',
        24 => '1edc17e7-dc26-67d8-95dd-3126b6164b0b',
        2 => '1edc17e7-d704-628c-8d2d-3126b6164b0b',
        7 => '1edc17e7-d760-6028-b363-3126b6164b0b',
    ];

    public function getPodcast(int $showId): ?string
    {
        if (isset(self::DAM_TO_ARTEMIS_MAP[$showId])) {
            return self::DAM_TO_ARTEMIS_MAP[$showId];
        }

        return null;
    }
}
