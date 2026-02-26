<?php

declare(strict_types=1);

namespace App\Tests\data\Model;

final readonly class AssetLicenceAdmUrl
{
    public static function createLicence(): string
    {
        return '/api/adm/v1/asset-licence';
    }

    public static function updateLicence(int $licenceId): string
    {
        return sprintf('/api/adm/v1/asset-licence/%d', $licenceId);
    }

    public static function getLicence(int $licenceId): string
    {
        return sprintf('/api/adm/v1/asset-licence/%d', $licenceId);
    }
}
