<?php

declare(strict_types=1);

namespace App\Model\Domain\User;

use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use AnzuSystems\CoreDamBundle\Entity\Traits\PersonNameTrait;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use Doctrine\Common\Collections\ArrayCollection;

abstract class AbstractUserDto
{
    use PersonNameTrait;

    #[Serialize]
    protected string $plainPassword;

    #[Serialize]
    protected bool $enabled;

    #[Serialize]
    protected bool $superAdmin;

    #[Serialize(handler: EntityIdHandler::class, type: ExtSystem::class)]
    protected ArrayCollection $adminToExtSystems;

    #[Serialize(handler: EntityIdHandler::class, type: AssetLicence::class)]
    protected ArrayCollection $assetLicences;

    #[Serialize]
    protected array $allowedAssetExternalProviders;

    #[Serialize]
    protected array $allowedDistributionServices;

    public function __construct()
    {
        $this->setPlainPassword('');
        $this->setEnabled(true);
        $this->setSuperAdmin(false);
        $this->setAdminToExtSystems(new ArrayCollection());
        $this->setAssetLicences(new ArrayCollection());
        $this->setAllowedAssetExternalProviders([]);
        $this->setAllowedDistributionServices([]);
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return ArrayCollection<int, ExtSystem>
     */
    public function getAdminToExtSystems(): ArrayCollection
    {
        return $this->adminToExtSystems;
    }

    public function setAdminToExtSystems(ArrayCollection $adminToExtSystems): self
    {
        $this->adminToExtSystems = $adminToExtSystems;

        return $this;
    }

    public function isSuperAdmin(): bool
    {
        return $this->superAdmin;
    }

    public function setSuperAdmin(bool $superAdmin): self
    {
        $this->superAdmin = $superAdmin;

        return $this;
    }

    /**
     * @return ArrayCollection<int, AssetLicence>
     */
    public function getAssetLicences(): ArrayCollection
    {
        return $this->assetLicences;
    }

    public function setAssetLicences(ArrayCollection $assetLicences): self
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
