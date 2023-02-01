<?php

declare(strict_types=1);

namespace App\Model\Domain\User;

use AnzuSystems\Contracts\AnzuApp;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\Entity\User;
use App\Model\Domain\AssetLicence\AssetLicenceDto;
use App\Model\Domain\ExtSystem\ExtSystemDto;
use Doctrine\Common\Collections\Collection;

final class CurrentUserDto
{
    #[Serialize(serializedName: 'id', handler: EntityIdHandler::class)]
    private User $user;

    public static function getInstance(User $user): self
    {
        return (new self())
            ->setUser($user)
        ;
    }

    #[Serialize(serializedName: 'id', handler: EntityIdHandler::class)]
    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    #[Serialize]
    public function getFirstName(): string
    {
        return $this->user->getFirstName();
    }

    #[Serialize]
    public function getLastName(): string
    {
        return $this->user->getLastName();
    }

    #[Serialize]
    public function getAllowedAssetExternalProviders(): array
    {
        return $this->user->getAllowedAssetExternalProviders();
    }

    #[Serialize]
    public function getAllowedDistributionServices(): array
    {
        return $this->user->getAllowedDistributionServices();
    }

    #[Serialize(type: AssetLicenceDto::class)]
    public function getAssetLicences(): Collection
    {
        return $this->user->getAssetLicences()->map(fn (AssetLicence $licence) => AssetLicenceDto::getInstance($licence));
    }

    #[Serialize(type: ExtSystemDto::class)]
    public function getUserToExtSystems(): Collection
    {
        return $this->user->getUserToExtSystems()->map(fn (ExtSystem $extSystem) => ExtSystemDto::getInstance($extSystem));
    }

    #[Serialize(type: ExtSystemDto::class)]
    public function getAdminToExtSystems(): Collection
    {
        return $this->user->getAdminToExtSystems()->map(fn (ExtSystem $extSystem) => ExtSystemDto::getInstance($extSystem));
    }

    #[Serialize]
    public function getSelectedLicence(): ?AssetLicenceDto
    {
        return $this->user->getSelectedLicence() ? AssetLicenceDto::getInstance($this->user->getSelectedLicence()) : null;
    }

    #[Serialize]
    public function isSuperAdmin(): bool
    {
        return $this->user->hasRole(User::ROLE_ADMIN);
    }

    #[Serialize]
    public function getEmail(): string
    {
        return $this->user->getEmail();
    }

    #[Serialize(strategy: Serialize::KEYS_VALUES)]
    public function getResolvedPermissions(): array
    {
        return $this->user->getResolvedPermissions();
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
