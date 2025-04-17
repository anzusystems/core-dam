<?php

declare(strict_types=1);

namespace App\Domain\ExtSystem;

final readonly class ExtSystemDailySportCallback extends AbstractExtSystemCallback
{
    public static function getDefaultKeyName(): string
    {
        return self::DAILY_SPORT_SLUG;
    }
}
