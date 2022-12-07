<?php

declare(strict_types=1);

namespace App\Domain\User;

use AnzuSystems\Contracts\Entity\AnzuUser;
use AnzuSystems\CoreDamBundle\Domain\AbstractManager;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use App\Entity\User;
use App\Model\Domain\User\AbstractUserDto;
use App\Model\Domain\User\CreateUserDto;
use App\Model\Domain\User\UpdateUserDto;
use Doctrine\Common\Collections\Collection;
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
            ->setFirstName($createUserDto->getFirstName())
            ->setLastName($createUserDto->getLastName())
            ->setEmail($createUserDto->getEmail())
            ->setAdminToExtSystems($createUserDto->getAdminToExtSystems())
            ->setAllowedAssetExternalProviders($createUserDto->getAllowedAssetExternalProviders())
            ->setAllowedDistributionServices($createUserDto->getAllowedDistributionServices())
        ;
        if ($createUserDto->isSuperAdmin()) {
            $user->setRoles([AnzuUser::ROLE_ADMIN]);
        }

        return $this->create($user, $flush);
    }

    public function updateFromDto(User $user, UpdateUserDto $updateUserDto, bool $flush = true): User
    {
        $user = $this->setPasswordToUserFromDto($user, $updateUserDto);
        $user
            ->setEnabled($updateUserDto->isEnabled())
            ->setFirstName($updateUserDto->getFirstName())
            ->setLastName($updateUserDto->getLastName())
            ->setPermissions($updateUserDto->getPermissions())
            ->setAllowedAssetExternalProviders($updateUserDto->getAllowedAssetExternalProviders())
            ->setAllowedDistributionServices($updateUserDto->getAllowedDistributionServices())
        ;
        $this->colUpdate(
            oldCollection: $user->getAdminToExtSystems(),
            newCollection: $updateUserDto->getAdminToExtSystems(),
            addElementFn: function (Collection $oldCollection, ExtSystem $newExtSystem) use ($user) {
                $newExtSystem->getAdminUsers()->add($user);
                $oldCollection->add($newExtSystem);
            },
            removeElementFn: function (Collection $oldCollection, ExtSystem $oldExtSystem) use ($user) {
                $oldExtSystem->getAdminUsers()->removeElement($user);
                $oldCollection->removeElement($oldExtSystem);
            }
        );
        $this->colUpdate(
            oldCollection: $user->getAssetLicences(),
            newCollection: $updateUserDto->getAssetLicences(),
            addElementFn: function (Collection $oldCollection, AssetLicence $newAssetLicence) use ($user) {
                $newAssetLicence->getUsers()->add($user);
                $oldCollection->add($newAssetLicence);
                if (false === $user->getUserToExtSystems()->containsKey((int) $newAssetLicence->getExtSystem()->getId())) {
                    $user->getUserToExtSystems()->add($newAssetLicence->getExtSystem());
                }
            },
            removeElementFn: function (Collection $oldCollection, AssetLicence $oldAssetLicence) use ($user) {
                $oldAssetLicence->getUsers()->removeElement($user);
                $oldCollection->removeElement($oldAssetLicence);
                if ($user->getUserToExtSystems()->containsKey((int) $oldAssetLicence->getExtSystem()->getId())) {
                    $user->getUserToExtSystems()->removeElement($oldAssetLicence->getExtSystem());
                }
            }
        );
        $user = $this->toggleSuperAdminRole($user, $updateUserDto->isSuperAdmin());

        return $this->updateExisting($user, $flush);
    }

    /**
     * Update user with fields from new user and persist it.
     */
    public function updateExisting(User $user, bool $flush = true): User
    {
        $this->trackModification($user);
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

    private function toggleSuperAdminRole(User $user, bool $isSuperAdmin): User
    {
        if ($isSuperAdmin) {
            return $user
                ->removeRole(AnzuUser::ROLE_USER)
                ->addRole(AnzuUser::ROLE_ADMIN)
            ;
        }

        return $user
            ->removeRole(AnzuUser::ROLE_ADMIN)
            ->addRole(AnzuUser::ROLE_USER)
         ;
    }
}
