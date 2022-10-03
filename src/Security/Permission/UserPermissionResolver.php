<?php

declare(strict_types=1);

namespace App\Security\Permission;

use AnzuSystems\Contracts\Entity\AnzuUser;
use App\Entity\PermissionGroup;
use App\Entity\User;

/**
 * Resolves user permissions from user's permissions groups and his own settings.
 */
final class UserPermissionResolver
{
    public static function resolve(User $user): array
    {
        // 1.If it's admin, return all permissions as granted
        if (in_array(AnzuUser::ROLE_ADMIN, $user->getRoles(), true)) {
            return DamPermissions::default(Grants::GRANT_ALLOW);
        }

        // 2. Take system default permission values and apply permissions from belonging groups
        $permissions = self::resolveForGroups($user->getPermissionGroups());

        // 3. At the end use those permissions but if user has some overridden, use them instead
        return array_merge($permissions, $user->getPermissions());
    }

    /**
     * @param PermissionGroup[] $permissionGroups
     * @psalm-param iterable<int, PermissionGroup> $permissionGroups
     */
    public static function resolveForGroups(iterable $permissionGroups): array
    {
        // 1. Take system default permission values
        $permissions = DamPermissions::default();

        // 2. Take permissions from user belonging permission groups and apply the highest grant
        foreach ($permissionGroups as $permissionGroup) {
            foreach ($permissionGroup->getPermissions() as $permissionName => $permissionValue) {
                if ($permissions[$permissionName] < $permissionValue) {
                    $permissions[$permissionName] = $permissionValue;
                }
            }
        }

        return $permissions;
    }
}
