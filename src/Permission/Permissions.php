<?php

declare(strict_types=1);

namespace App\Permission;

use AnzuSystems\CoreDamBundle\Permission\DamPermissions;

final class Permissions
{
    public const GRANTS = 'grants';
    public const UI = 'ui';
    public const UI_CATEGORY = 'category';
    public const UI_GROUP = 'group';

    public static function allDetail(): array
    {
        return [
            DamPermissions::DAM_ASSET_VIEW => [
                self::GRANTS => [Grants::GRANT_ALLOW, Grants::GRANT_DENY],
                self::UI => [
                    self::UI_CATEGORY => UiCategories::CATEGORY_ADMIN,
                    self::UI_GROUP => UiGroups::GROUP_ASSET,
                ],
            ],
        ];
    }

    public static function all(): array
    {
        return array_merge(
            DamPermissions::ALL,
        );
    }

    public static function default(int $defaultGrant = Grants::GRANT_DENY): array
    {
        $resolved = [];
        foreach (self::all() as $permission) {
            $resolved[$permission] = $defaultGrant;
        }

        return $resolved;
    }

    public static function permissionAllowedValues(): array
    {
        $out = [];
        foreach (self::allDetail() as $permissionName => $detail) {
            $out[$permissionName] = $detail[self::GRANTS];
        }

        return $out;
    }
}
