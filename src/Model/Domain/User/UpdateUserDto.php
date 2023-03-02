<?php

declare(strict_types=1);

namespace App\Model\Domain\User;

use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class UpdateUserDto
{
    #[Serialize(handler: EntityIdHandler::class, type: ExtSystem::class)]
    protected Collection $adminToExtSystems;

    #[Serialize(handler: EntityIdHandler::class, type: AssetLicence::class)]
    protected Collection $assetLicences;

    #[Serialize]
    protected array $allowedAssetExternalProviders;

    #[Serialize]
    protected array $allowedDistributionServices;

    public function __construct()
    {
        $this->setAdminToExtSystems(new ArrayCollection());
        $this->setAssetLicences(new ArrayCollection());
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
