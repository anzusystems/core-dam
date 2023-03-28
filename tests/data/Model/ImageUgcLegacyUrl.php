<?php

declare(strict_types=1);

namespace App\Tests\data\Model;

use App\DataFixtures\AssetLicenceFixtures;

final readonly class ImageUgcLegacyUrl
{
    public static function getCreatePath(int $licenceId = AssetLicenceFixtures::BLOG_ONE_EXT_ID): string
    {
        return "/api/ugc/vlegacy/blog/$licenceId/image";
    }

    public static function getCreateChunkPath(string $imageFileId): string
    {
        return "/api/ugc/vlegacy/image/$imageFileId/chunk";
    }

    public static function getFinishUploadPath(string $imageFileId): string
    {
        return "/api/ugc/vlegacy/image/$imageFileId/uploaded";
    }

    public static function getImageRotatePath(string $imageFileId, int $rotate): string
    {
        return "/api/ugc/vlegacy/image/$imageFileId/rotate/$rotate";
    }

    public static function getSingleImagePath(string $imageFileId): string
    {
        return "/api/ugc/vlegacy/image/$imageFileId";
    }

    public static function getUpdateImagePath(string $imageFileId): string
    {
        return "/api/ugc/vlegacy/image/$imageFileId";
    }

    public static function getUpdateBulkImagePath(): string
    {
        return "/api/ugc/vlegacy/image/bulk-update";
    }

    public static function getUpdateBulkUndescribedImagePath(): string
    {
        return "/api/ugc/vlegacy/image/bulk-update-undescribed";
    }

    public static function getImageSearchListPath(int $licenceId = AssetLicenceFixtures::BLOG_ONE_EXT_ID): string
    {
        return "/api/ugc/vlegacy/blog/$licenceId/image";
    }
}
