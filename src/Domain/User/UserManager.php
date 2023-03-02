<?php

declare(strict_types=1);

namespace App\Domain\User;

use AnzuSystems\AuthBundle\Model\SsoUserDto;
use AnzuSystems\CommonBundle\Domain\User\AbstractUserManager;
use AnzuSystems\CommonBundle\Model\User\UserDto;
use AnzuSystems\Contracts\Entity\AnzuUser;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use App\Entity\User;
use App\Model\Domain\User\UpdateCurrentUserDto;
use App\Model\Domain\User\UpdateUserDto;
use Doctrine\Common\Collections\Collection;

/**
 * User persistence management.
 */
final class UserManager extends AbstractUserManager
{
    public function createFromSsoUserInfo(SsoUserDto $ssoUserDto, array $roles = [User::ROLE_UGC], bool $flush = false): AnzuUser
    {
        $userDto = (new UserDto())
            ->setId((int) $ssoUserDto->getId())
            ->setEmail($ssoUserDto->getEmail())
            ->setRoles($roles)
        ;

        return $this->createAnzuUser(new User(), $userDto, $flush);
    }

    public function updateFromUserDto(User $user, UpdateUserDto $updateUserDto, bool $flush = true): User
    {
        $user
            ->setAllowedAssetExternalProviders($updateUserDto->getAllowedAssetExternalProviders())
            ->setAllowedDistributionServices($updateUserDto->getAllowedDistributionServices());
        $user = $this->assignLicencesAndExtSystems($user, $updateUserDto);

        return $this->updateExisting($user, $flush);
    }

    public function updateFromCurrentUserDto(User $user, UpdateCurrentUserDto $currentUserDto, bool $flush = true): User
    {
        $user->setSelectedLicence($currentUserDto->getSelectedLicence());

        return $this->updateExisting($user, $flush);
    }

    /**
     * Delete user from persistence.
     */
    public function delete(User $user, bool $flush = true): void
    {
        $this->entityManager->remove($user);
        $this->flush($flush);
    }

    /**
     * Update user with fields from new user and persist it.
     */
    private function updateExisting(User $user, bool $flush = true): User
    {
        $this->trackModification($user);
        $this->flush($flush);

        return $user;
    }

    private function assignLicencesAndExtSystems(User $user, UpdateUserDto $userDto): User
    {
        /** @psalm-suppress InvalidArgument */
        $this->colUpdate(
            oldCollection: $user->getAdminToExtSystems(),
            newCollection: $userDto->getAdminToExtSystems(),
            addElementFn: function (Collection $oldCollection, ExtSystem $newExtSystem) use ($user): bool {
                $newExtSystem->getAdminUsers()->add($user);
                $oldCollection->add($newExtSystem);

                return true;
            },
            removeElementFn: function (Collection $oldCollection, ExtSystem $oldExtSystem) use ($user): bool {
                $oldExtSystem->getAdminUsers()->removeElement($user);
                $oldCollection->removeElement($oldExtSystem);

                return true;
            }
        );
        /** @psalm-suppress InvalidArgument */
        $this->colUpdate(
            oldCollection: $user->getAssetLicences(),
            newCollection: $userDto->getAssetLicences(),
            addElementFn: function (Collection $oldCollection, AssetLicence $newAssetLicence) use ($user): bool {
                $newAssetLicence->getUsers()->add($user);
                $oldCollection->add($newAssetLicence);
                if (false === $user->getUserToExtSystems()->containsKey((int) $newAssetLicence->getExtSystem()->getId())) {
                    $user->getUserToExtSystems()->add($newAssetLicence->getExtSystem());
                }
                if (null === $user->getSelectedLicence()) {
                    $user->setSelectedLicence($newAssetLicence);
                }

                return true;
            },
            removeElementFn: function (Collection $oldCollection, AssetLicence $oldAssetLicence) use ($user): bool {
                $oldAssetLicence->getUsers()->removeElement($user);
                $oldCollection->removeElement($oldAssetLicence);
                if ($user->getUserToExtSystems()->containsKey((int) $oldAssetLicence->getExtSystem()->getId())) {
                    $user->getUserToExtSystems()->removeElement($oldAssetLicence->getExtSystem());
                }
                if ($user->getSelectedLicence() instanceof AssetLicence && $oldAssetLicence->is($user->getSelectedLicence())) {
                    $user->setSelectedLicence($oldCollection->first() ?: null);
                }

                return true;
            }
        );

        return $user;
    }
}
