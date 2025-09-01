<?php

declare(strict_types=1);

namespace App\Model\Domain\User;

use AnzuSystems\CommonBundle\Model\User\UserDto;
use AnzuSystems\Contracts\Entity\AnzuUser;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\AssetLicenceGroup;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class DamUserDto extends UserDto
{
    #[Serialize(handler: EntityIdHandler::class, type: ExtSystem::class)]
    protected Collection $adminToExtSystems;

    #[Serialize(handler: EntityIdHandler::class, type: ExtSystem::class)]
    protected Collection $userToExtSystems;

    #[Serialize(handler: EntityIdHandler::class, type: AssetLicence::class)]
    protected Collection $assetLicences;

    #[Serialize(handler: EntityIdHandler::class, type: AssetLicenceGroup::class)]
    protected Collection $licenceGroups;

    #[Serialize]
    protected array $allowedAssetExternalProviders = [];

    #[Serialize]
    protected array $allowedDistributionServices = [];

    public function __construct()
    {
        parent::__construct();
        $this->adminToExtSystems = new ArrayCollection();
        $this->userToExtSystems = new ArrayCollection();
        $this->assetLicences = new ArrayCollection();
        $this->licenceGroups = new ArrayCollection();
    }

    public static function createFromUser(AnzuUser|User $user): static
    {
        if ($user instanceof User) {
            /** @psalm-suppress UndefinedMethod */
            $instance = parent::createFromUser($user);
            $instance->setAllowedDistributionServices($user->getAllowedDistributionServices());
            $instance->setAdminToExtSystems($user->getAdminToExtSystems());
            $instance->setAssetLicences($user->getAssetLicences());
            $instance->setUserToExtSystems($user->getUserToExtSystems());
            $instance->setLicenceGroups($user->getLicenceGroups());
            $instance->setAllowedAssetExternalProviders($user->getAllowedAssetExternalProviders());
            return $instance;
        }

        return parent::createFromUser($user);
    }

    /**
     * @return Collection<int, ExtSystem>
     */
    public function getAdminToExtSystems(): Collection
    {
        return $this->adminToExtSystems;
    }

    public function setAdminToExtSystems(Collection $adminToExtSystems): self
    {
        $this->adminToExtSystems = $adminToExtSystems;

        return $this;
    }

    /**
     * @return Collection<int, AssetLicence>
     */
    public function getAssetLicences(): Collection
    {
        return $this->assetLicences;
    }

    public function setAssetLicences(Collection $assetLicences): self
    {
        $this->assetLicences = $assetLicences;

        return $this;
    }

    /**
     * @return Collection<int, AssetLicenceGroup>
     */
    public function getLicenceGroups(): Collection
    {
        return $this->licenceGroups;
    }

    /**
     * @param Collection<int, AssetLicenceGroup> $licenceGroups
     */
    public function setLicenceGroups(Collection $licenceGroups): self
    {
        $this->licenceGroups = $licenceGroups;
        return $this;
    }

    public function getAllowedAssetExternalProviders(): array
    {
        return $this->allowedAssetExternalProviders;
    }

    public function setAllowedAssetExternalProviders(array $allowedAssetExternalProviders): self
    {
        $this->allowedAssetExternalProviders = $allowedAssetExternalProviders;

        return $this;
    }

    public function getAllowedDistributionServices(): array
    {
        return $this->allowedDistributionServices;
    }

    public function setAllowedDistributionServices(array $allowedDistributionServices): self
    {
        $this->allowedDistributionServices = $allowedDistributionServices;

        return $this;
    }

    public function getUserToExtSystems(): Collection
    {
        return $this->userToExtSystems;
    }

    public function setUserToExtSystems(Collection $userToExtSystems): self
    {
        $this->userToExtSystems = $userToExtSystems;
        return $this;
    }
}
