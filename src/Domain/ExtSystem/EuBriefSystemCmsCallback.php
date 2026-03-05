<?php

declare(strict_types=1);

namespace App\Domain\ExtSystem;

final readonly class EuBriefSystemCmsCallback extends AbstractExtSystemCallback
{
    public static function getDefaultKeyName(): string
    {
        return self::EU_BRIEF_SLUG;
    }
}
