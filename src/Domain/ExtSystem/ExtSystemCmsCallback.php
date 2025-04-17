<?php

declare(strict_types=1);

namespace App\Domain\ExtSystem;

final readonly class ExtSystemCmsCallback extends AbstractExtSystemCallback
{
    public static function getDefaultKeyName(): string
    {
        return self::CMS_SLUG;
    }
}
