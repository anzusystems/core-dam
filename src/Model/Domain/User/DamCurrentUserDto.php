<?php

declare(strict_types=1);

namespace App\Model\Domain\User;

use AnzuSystems\Contracts\AnzuApp;
use AnzuSystems\Contracts\Entity\AnzuUser;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\AssetLicenceGroup;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\Entity\User;
use App\Model\Domain\AssetLicence\AssetLicenceDto;
use App\Model\Domain\AssetLicenceGroup\AssetLicenceGroupDto;
use App\Model\Domain\ExtSystem\ExtSystemDto;
use App\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\Collection;

#[AppAssert\UserSelectedLicence]
final class DamCurrentUserDto extends DamUserDto
{
    #[Serialize(handler: EntityIdHandler::class)]
    private ?AssetLicence $selectedLicence;

    public static function createFromUser(AnzuUser|User $user): static
    {
        if ($user instanceof User) {
            return parent::createFromUser($user)
                ->setSelectedLicence($user->getSelectedLicence())
            ;
        }

        return parent::createFromUser($user);
    }
    #[Serialize(type: AssetLicenceDto::class)]
    public function getAssetLicencesDto(): Collection
    {
        return $this->assetLicences->map(fn (AssetLicence $licence) => AssetLicenceDto::getInstance($licence));
    }

    #[Serialize(type: ExtSystemDto::class)]
    public function getUserToExtSystemsDto(): Collection
    {
        return $this->userToExtSystems->map(fn (ExtSystem $extSystem) => ExtSystemDto::getInstance($extSystem));
    }

    #[Serialize(type: ExtSystemDto::class)]
    public function getAdminToExtSystemsDto(): Collection
    {
        return $this->adminToExtSystems->map(fn (ExtSystem $extSystem) => ExtSystemDto::getInstance($extSystem));
    }

    #[Serialize(type: AssetLicenceGroupDto::class)]
    public function getLicenceGroupsDto(): Collection
    {
        return $this->licenceGroups->map(fn (AssetLicenceGroup $group) => AssetLicenceGroupDto::getInstance($group));
    }

    #[Serialize]
    public function getSelectedLicenceDto(): ?AssetLicenceDto
    {
        return $this->selectedLicence ? AssetLicenceDto::getInstance($this->selectedLicence) : null;
    }

    public function getSelectedLicence(): ?AssetLicence
    {
        return $this->selectedLicence;
    }

    public function setSelectedLicence(?AssetLicence $selectedLicence): self
    {
        $this->selectedLicence = $selectedLicence;

        return $this;
    }

    #[Serialize(serializedName: '_resourceName')]
    public function getResourceName(): string
    {
        return User::getResourceName();
    }

    #[Serialize(serializedName: '_system')]
    public static function getSystem(): string
    {
        return AnzuApp::getAppSystem();
    }
}
