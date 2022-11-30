<?php

declare(strict_types=1);

namespace App\Domain\PermissionGroup;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CoreDamBundle\Validator\EntityValidator;
use App\Entity\PermissionGroup;
use App\Security\Permission\DamPermissions;

/**
 * Complete PermissionGroup processing.
 */
final class PermissionGroupFacade
{
    public function __construct(
        private readonly EntityValidator $validator,
        private readonly PermissionGroupManager $permissionGroupManager,
    ) {
    }

    /**
     * Process new PermissionGroup creation.
     *
     * @throws ValidationException
     */
    public function create(PermissionGroup $permissionGroup): PermissionGroup
    {
        $permissionGroup->setPermissions(DamPermissions::default());
        $this->validator->validate($permissionGroup);
        $this->permissionGroupManager->create($permissionGroup);

        return $permissionGroup;
    }

    /**
     * Process updating of PermissionGroup.
     *
     * @throws ValidationException
     */
    public function update(
        PermissionGroup $permissionGroup,
        PermissionGroup $newPermissionGroup
    ): PermissionGroup {
        $this->validator->validate($newPermissionGroup, $permissionGroup);
        $this->permissionGroupManager->update($permissionGroup, $newPermissionGroup);

        return $permissionGroup;
    }

    /**
     * Process deletion of PermissionGroup.
     */
    public function delete(PermissionGroup $permissionGroup): bool
    {
        return $this->permissionGroupManager->delete($permissionGroup);
    }
}
