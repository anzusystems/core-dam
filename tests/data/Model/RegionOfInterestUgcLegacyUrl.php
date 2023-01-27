<?php

declare(strict_types=1);

namespace App\Tests\data\Model;

final readonly class RegionOfInterestUgcLegacyUrl
{
    public static function getListPath(string $imageFileId): string
    {
        return "/api/ugc/vlegacy/image/$imageFileId/roi";
    }

    public static function getOnePath(string $roiId): string
    {
        return "/api/ugc/vlegacy/roi/$roiId";
    }

    public static function getUpdatePath(string $roiId): string
    {
        return "/api/ugc/vlegacy/roi/$roiId";
    }
}
