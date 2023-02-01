<?php

declare(strict_types=1);

namespace App\Security\Voter;

use AnzuSystems\CommonBundle\Security\Voter\AbstractVoter;
use App\Security\Permission\DamPermissions;

final class BaseVoter extends AbstractVoter
{
    protected function getSupportedPermissions(): array
    {
        return [
            DamPermissions::DAM_USER_CREATE,
            DamPermissions::DAM_USER_UPDATE,
            DamPermissions::DAM_USER_VIEW,
            DamPermissions::DAM_PERMISSION_GROUP_CREATE,
            DamPermissions::DAM_PERMISSION_GROUP_UPDATE,
            DamPermissions::DAM_PERMISSION_GROUP_VIEW,
            DamPermissions::DAM_PERMISSION_GROUP_DELETE,
        ];
    }
}
