<?php

declare(strict_types=1);

namespace App\Domain\User;

use AnzuSystems\CommonBundle\Domain\User\AbstractUserManager;
use AnzuSystems\Contracts\Entity\Embeds\Avatar;
use AnzuSystems\Contracts\Entity\Embeds\Person;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\AssetLicenceGroup;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use App\Entity\User;
use App\Model\Domain\User\DamUserDto;
use App\Model\Domain\User\UpdateCurrentUserDto;
use Doctrine\Common\Collections\Collection;

final class UserManager extends AbstractUserManager
{
    public function updateFromDamUserDto(User $user, DamUserDto $updateUserDto, bool $flush = true): User
    {
        $this->updateAnzuUser($user, $updateUserDto, false);
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

    public function deletePersonalData(User $user): User
    {
        return $user
            ->setEnabled(false)
            ->setAvatar(new Avatar())
            ->setPerson(new Person())
            ->setSelectedLicence(null)
        ;
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

    private function assignLicencesAndExtSystems(User $user, DamUserDto $userDto): User
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
            oldCollection: $user->getLicenceGroups(),
            newCollection: $userDto->getLicenceGroups(),
            addElementFn: function (Collection $oldCollection, AssetLicenceGroup $assetLicenceGroup) use ($user): bool {
                $assetLicenceGroup->getUsers()->add($user);
                $oldCollection->add($assetLicenceGroup);

                if (false === $user->getUserToExtSystems()->containsKey((int) $assetLicenceGroup->getExtSystem()->getId())) {
                    $user->getUserToExtSystems()->add($assetLicenceGroup->getExtSystem());
                }

                return true;
            },
            removeElementFn: function (Collection $oldCollection, AssetLicenceGroup $oldAssetLicenceGroup) use ($user): bool {
                $oldAssetLicenceGroup->getUsers()->removeElement($user);
                $oldCollection->removeElement($oldAssetLicenceGroup);

                if ($user->getUserToExtSystems()->containsKey((int) $oldAssetLicenceGroup->getExtSystem()->getId())) {
                    $user->getUserToExtSystems()->removeElement($oldAssetLicenceGroup->getExtSystem());
                }
                if ($user->getSelectedLicence() instanceof AssetLicence && $oldAssetLicenceGroup->getLicences()->contains($user->getSelectedLicence())) {
                    $user->setSelectedLicence(null);
                }

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

        if (null === $user->getSelectedLicence()) {
            $user->setSelectedLicence($this->findFirstLicence($user));
        }

        return $user;
    }

    private function findFirstLicence(User $user): ?AssetLicence
    {
        foreach ($user->getLicenceGroups() as $group) {
            $licence = $group->getLicences()->first();
            if ($licence instanceof AssetLicence) {
                return $licence;
            }
        }

        $licence = $user->getAssetLicences()->first();
        if ($licence instanceof AssetLicence) {
            return $licence;
        }

        return null;
    }
}
