<?php

declare(strict_types=1);

namespace App\Security\Permission;

use AnzuSystems\CoreDamBundle\Security\Permission\DamPermissions as BaseDamPermissions;
use AnzuSystems\CoreDamBundle\Security\Permission\Grants;

final class DamPermissions extends BaseDamPermissions
{
    // User
    public const DAM_USER_CREATE = 'dam_user_create';
    public const DAM_USER_UPDATE = 'dam_user_update';
    public const DAM_USER_VIEW = 'dam_user_view';
    public const DAM_USER_UGC_IMPERSONATE = 'dam_user_ugcImpersonate';
    public const DAM_USER_UI = 'dam_user_ui';

    // PermissionGroup
    public const DAM_PERMISSION_GROUP_CREATE = 'dam_permissionGroup_create';
    public const DAM_PERMISSION_GROUP_UPDATE = 'dam_permissionGroup_update';
    public const DAM_PERMISSION_GROUP_VIEW = 'dam_permissionGroup_view';
    public const DAM_PERMISSION_GROUP_DELETE = 'dam_permissionGroup_delete';
    public const DAM_PERMISSION_GROUP_UI = 'dam_permissionGroup_ui';

    public static function all(): array
    {
        return [
            ...BaseDamPermissions::ALL,
            ...[
                self::DAM_USER_VIEW,
                self::DAM_USER_CREATE,
                self::DAM_USER_UPDATE,
                self::DAM_USER_UGC_IMPERSONATE,
                self::DAM_USER_UI,
                self::DAM_PERMISSION_GROUP_VIEW,
                self::DAM_PERMISSION_GROUP_CREATE,
                self::DAM_PERMISSION_GROUP_UPDATE,
                self::DAM_PERMISSION_GROUP_DELETE,
                self::DAM_PERMISSION_GROUP_UI,
            ],
        ];
    }

    public static function default(int $defaultGrant = Grants::GRANT_DENY): array
    {
        $resolved = [];
        foreach (self::all() as $permission) {
            $resolved[$permission] = $defaultGrant;
        }

        return $resolved;
    }
}
