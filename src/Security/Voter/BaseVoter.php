<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Security\Permission\DamPermissions;
use App\Security\Permission\UiPermissions;

final class BaseVoter extends AbstractVoter
{
    protected function getSupportedPermissions(): array
    {
        return array_merge([
            DamPermissions::DAM_CUSTOM_FORM_CREATE,
            DamPermissions::DAM_CUSTOM_FORM_UPDATE,
            DamPermissions::DAM_CUSTOM_FORM_VIEW,
            DamPermissions::DAM_REGION_OF_INTEREST_CREATE,
            DamPermissions::DAM_REGION_OF_INTEREST_UPDATE,
            DamPermissions::DAM_REGION_OF_INTEREST_VIEW,
            DamPermissions::DAM_REGION_OF_INTEREST_DELETE,
            DamPermissions::DAM_EXT_SYSTEM_UPDATE,
            DamPermissions::DAM_EXT_SYSTEM_VIEW,
            DamPermissions::DAM_ASSET_LICENCE_CREATE,
            DamPermissions::DAM_ASSET_LICENCE_UPDATE,
            DamPermissions::DAM_ASSET_LICENCE_VIEW,
            DamPermissions::DAM_USER_VIEW,
            DamPermissions::DAM_USER_CREATE,
            DamPermissions::DAM_USER_UPDATE,
            DamPermissions::DAM_PERMISSION_GROUP_VIEW,
            DamPermissions::DAM_PERMISSION_GROUP_CREATE,
            DamPermissions::DAM_PERMISSION_GROUP_UPDATE,
            DamPermissions::DAM_PERMISSION_GROUP_DELETE,
            DamPermissions::DAM_AUTHOR_CREATE,
            DamPermissions::DAM_AUTHOR_VIEW,
            DamPermissions::DAM_AUTHOR_UPDATE,
            DamPermissions::DAM_AUTHOR_DELETE,
        ], UiPermissions::ALL);
    }
}
