<?php

declare(strict_types=1);

namespace App\Security\Permission;

use AnzuSystems\CoreDamBundle\Security\Permission\DamPermissions as BaseDamPermissions;

final class DamPermissions extends BaseDamPermissions
{
    // User
    public const DAM_USER_CREATE = 'dam_user_create';
    public const DAM_USER_UPDATE = 'dam_user_update';
    public const DAM_USER_VIEW = 'dam_user_view';
    public const DAM_USER_UGC_IMPERSONATE = 'dam_user_ugcImpersonate';
    public const DAM_USER_UI = 'dam_user_ui';

    public const ALL = [
        ...parent::ALL,
        self::DAM_USER_VIEW,
        self::DAM_USER_CREATE,
        self::DAM_USER_UPDATE,
        self::DAM_USER_UGC_IMPERSONATE,
        self::DAM_USER_UI,
    ];
}
