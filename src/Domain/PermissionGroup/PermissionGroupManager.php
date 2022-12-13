<?php

declare(strict_types=1);

namespace App\Domain\PermissionGroup;

use AnzuSystems\CoreDamBundle\Domain\AbstractManager;
use App\Entity\PermissionGroup;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;

/**
 * PermissionGroup persistence management.
 */
final class PermissionGroupManager extends AbstractManager
{
    /**
     * Persist new PermissionGroup.
     */
    public function create(PermissionGroup $permissionGroup, bool $flush = true): PermissionGroup
    {
        $this->trackCreation($permissionGroup);
        $this->entityManager->persist($permissionGroup);
        $this->flush($flush);

        return $permissionGroup;
    }

    /**
     * Update PermissionGroup and persist it.
     */
    public function update(
        PermissionGroup $permissionGroup,
        PermissionGroup $newPermissionGroup,
        bool $flush = true
    ): PermissionGroup {
        $this->trackModification($permissionGroup);
        $permissionGroup->setPermissions($newPermissionGroup->getPermissions());
        $permissionGroup->setTitle($newPermissionGroup->getTitle());
        $permissionGroup->setDescription($newPermissionGroup->getDescription());
        $this->colUpdate(
            oldCollection: $permissionGroup->getUsers(),
            newCollection: $newPermissionGroup->getUsers(),
            addElementFn: function (Collection $oldCollection, User $newUser) use ($permissionGroup) {
                $newUser->getPermissionGroups()->add($permissionGroup);
                $oldCollection->add($newUser);
            },
            removeElementFn: function (Collection $oldCollection, User $oldUser) use ($permissionGroup) {
                $oldUser->getPermissionGroups()->removeElement($permissionGroup);
                $oldCollection->removeElement($oldUser);
            }
        );

        $this->flush($flush);

        return $permissionGroup;
    }

    /**
     * Delete PermissionGroup from persistence.
     */
    public function delete(PermissionGroup $permissionGroup, bool $flush = true): bool
    {
        $this->entityManager->remove($permissionGroup);
        $this->flush($flush);

        return true;
    }
}
