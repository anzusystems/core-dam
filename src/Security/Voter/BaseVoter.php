<?php

declare(strict_types=1);

namespace App\Security\Voter;

use AnzuSystems\CoreDamBundle\Security\Voter\AbstractVoter;
use App\Security\Permission\DamPermissions;
use App\Security\Permission\UiPermissions;

final class BaseVoter extends AbstractVoter
{
    protected function getSupportedPermissions(): array
    {
        return [
            ...[
                DamPermissions::DAM_USER_CREATE,
                DamPermissions::DAM_USER_UPDATE,
                DamPermissions::DAM_USER_VIEW,
                DamPermissions::DAM_PERMISSION_GROUP_CREATE,
                DamPermissions::DAM_PERMISSION_GROUP_UPDATE,
                DamPermissions::DAM_PERMISSION_GROUP_READ,
                DamPermissions::DAM_PERMISSION_GROUP_DELETE,
            ],
            ...UiPermissions::ALL,
        ];
    }
}
