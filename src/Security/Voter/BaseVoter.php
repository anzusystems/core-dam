<?php

declare(strict_types=1);

namespace App\Security\Voter;

use AnzuSystems\CoreDamBundle\Permission\DamPermissions;

final class BaseVoter extends AbstractVoter
{
    protected function getSupportedPermissions(): array
    {
        return [
            DamPermissions::DAM_ASSET_VIEW,
        ];
    }
}
