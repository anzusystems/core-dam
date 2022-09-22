<?php

declare(strict_types=1);

namespace App\Domain\User;

use AnzuSystems\CoreDamBundle\Domain\AbstractManager;
use App\Entity\User;

/**
 * User persistence management.
 */
final class UserManager extends AbstractManager
{
    /**
     * Create a new user and persist it.
     */
    public function create(User $user, bool $flush = true): User
    {
        $this->trackCreation($user);
        $this->entityManager->persist($user);
        $this->flush($flush);

        return $user;
    }

    /**
     * Update user with fields from new user and persist it.
     */
    public function update(User $user, User $newUser, bool $flush = true): User
    {
        $this->trackModification($user);
        $user
            ->setRoles($newUser->getRoles())
            ->setEmail($newUser->getEmail())
            ->setPermissions($newUser->getPermissions())
            ->setEnabled($newUser->isEnabled())
        ;
        $this->flush($flush);

        return $user;
    }

    /**
     * Delete user from persistence.
     */
    public function delete(User $user, bool $flush = true): void
    {
        $this->entityManager->remove($user);
        $this->flush($flush);
    }
}
