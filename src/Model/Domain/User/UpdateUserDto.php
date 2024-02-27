<?php

declare(strict_types=1);

namespace App\Model\Domain\User;

use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\AssetLicenceGroup;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class UpdateUserDto
{
    #[Serialize(handler: EntityIdHandler::class, type: ExtSystem::class)]
    private Collection $adminToExtSystems;

    #[Serialize(handler: EntityIdHandler::class, type: AssetLicence::class)]
    private Collection $assetLicences;

    #[Serialize(handler: EntityIdHandler::class, type: AssetLicenceGroup::class)]
    private Collection $licenceGroups;

    #[Serialize]
    private array $allowedAssetExternalProviders;

    #[Serialize]
    private array $allowedDistributionServices;

    public function __construct()
    {
        $this->setAdminToExtSystems(new ArrayCollection());
        $this->setAssetLicences(new ArrayCollection());
        $this->setLicenceGroups(new ArrayCollection());
        $this->setAllowedAssetExternalProviders([]);
        $this->setAllowedDistributionServices([]);
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
}
