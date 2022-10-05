<?php

declare(strict_types=1);

namespace App\Domain\User;

use AnzuSystems\CoreDamBundle\Domain\AbstractManager;
use App\Entity\User;
use App\Model\Domain\User\AbstractUserDto;
use App\Model\Domain\User\CreateUserDto;
use App\Model\Domain\User\UpdateUserDto;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * User persistence management.
 */
final class UserManager extends AbstractManager
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

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

    public function createFromDto(CreateUserDto $createUserDto, bool $flush = true): User
    {
        $user = new User();
        $user = $this->setPasswordToUserFromDto($user, $createUserDto);
        $user
            ->setEnabled($createUserDto->isEnabled())
            ->setEmail($createUserDto->getEmail())
        ;

        return $this->create($user, $flush);
    }

    public function updateFromDto(User $user, UpdateUserDto $updateUserDto, bool $flush = true): User
    {
        $user = $this->setPasswordToUserFromDto($user, $updateUserDto);
        $user
            ->setEnabled($updateUserDto->isEnabled())
        ;

        return $this->update($user, $user, $flush);
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

    private function setPasswordToUserFromDto(User $user, AbstractUserDto $userDto): User
    {
        if (empty($userDto->getPlainPassword())) {
            return $user;
        }
        $password = $this->userPasswordHasher->hashPassword(
            $user,
            $userDto->getPlainPassword()
        );

        return $user->setPassword($password);
    }
}
