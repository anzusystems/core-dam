<?php

declare(strict_types=1);

namespace App\Tests\data\Model;

final readonly class AssetLicenceSysUrl
{
    public static function upsertLicence(): string
    {
        return "/api/sys/v1/asset-licence";
    }
}
