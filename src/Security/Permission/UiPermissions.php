<?php

declare(strict_types=1);

namespace App\Security\Permission;

final class UiPermissions
{
    public const DAM_USER_UI = 'dam_user_ui';
    public const DAM_PERMISSION_GROUP_UI = 'dam_permissionGroup_ui';
    public const DAM_EXT_SYSTEM_UI = 'dam_extSystem_ui';
    public const DAM_ASSET_LICENCE_UI = 'dam_assetLicence_ui';

    public const ALL = [
        self::DAM_USER_UI,
        self::DAM_PERMISSION_GROUP_UI,
        self::DAM_EXT_SYSTEM_UI,
        self::DAM_ASSET_LICENCE_UI,
    ];
}
