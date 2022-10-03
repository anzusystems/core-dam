<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Security\Permission\DamPermissions;

final class BaseVoter extends AbstractVoter
{
    protected function getSupportedPermissions(): array
    {
        return DamPermissions::all();
    }
}
