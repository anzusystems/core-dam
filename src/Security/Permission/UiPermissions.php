<?php

declare(strict_types=1);

namespace App\Security\Permission;

final class UiPermissions
{
    public const DAM_USER_UI = 'dam_user_ui';
    public const DAM_PERMISSION_GROUP_UI = 'dam_permissionGroup_ui';
    public const DAM_EXT_SYSTEM_UI = 'dam_extSystem_ui';
    public const DAM_ASSET_LICENCE_UI = 'dam_assetLicence_ui';
    public const DAM_AUTHOR_UI = 'dam_author_ui';
    public const DAM_KEYWORD_UI = 'dam_keyword_ui';
    public const DAM_DISTRIBUTION_CATEGORY_UI = 'dam_distributionCategory_ui';
    public const DAM_DISTRIBUTION_CATEGORY_SELECT_UI = 'dam_distributionCategorySelect_ui';
    public const ADMIN_LOG_UI = 'admin_log_ui';

    public const ALL = [
        self::DAM_USER_UI,
        self::DAM_PERMISSION_GROUP_UI,
        self::DAM_EXT_SYSTEM_UI,
        self::DAM_ASSET_LICENCE_UI,
        self::DAM_AUTHOR_UI,
        self::DAM_KEYWORD_UI,
        self::DAM_DISTRIBUTION_CATEGORY_UI,
        self::DAM_DISTRIBUTION_CATEGORY_SELECT_UI,
        self::ADMIN_LOG_UI,
    ];
}
