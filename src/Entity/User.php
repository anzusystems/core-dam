<?php

declare(strict_types=1);

namespace App\Entity;

use AnzuSystems\AuthBundle\Contracts\AnzuAuthUserInterface;
use AnzuSystems\AuthBundle\Contracts\ApiTokenUserInterface;
use AnzuSystems\Contracts\Entity\Interfaces\TimeTrackingInterface;
use AnzuSystems\Contracts\Entity\Interfaces\UserTrackingInterface;
use AnzuSystems\Contracts\Entity\Traits\TimeTrackingTrait;
use AnzuSystems\CoreDamBundle\App;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\DamUser;
use AnzuSystems\CoreDamBundle\Entity\PermissionGroup;
use AnzuSystems\CoreDamBundle\Entity\Traits\PersonNameTrait;
use AnzuSystems\CoreDamBundle\Entity\Traits\UserTrackingTrait;
use AnzuSystems\CoreDamBundle\Validator\Constraints\UniqueEntity;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_email', fields: ['email'])]
#[UniqueEntity(fields: ['id'])]
#[UniqueEntity(fields: ['email'])]
class User extends DamUser implements
    AnzuAuthUserInterface,
    ApiTokenUserInterface,
    UserTrackingInterface,
    TimeTrackingInterface
{
    use UserTrackingTrait;
    use TimeTrackingTrait;
    use PersonNameTrait;

    public const ID_ANONYMOUS = 1_763_600;
    public const ID_CONSOLE = 1_000_000;
    public const ID_ADMIN = 10_001_039;

    public const ROLE_UGC = 'ROLE_UGC';

    #[ORM\ManyToMany(targetEntity: PermissionGroup::class, inversedBy: 'users', fetch: App::DOCTRINE_EXTRA_LAZY, indexBy: 'id')]
    #[ORM\JoinTable]
    #[Serialize(handler: EntityIdHandler::class, type: PermissionGroup::class)]
    protected Collection $permissionGroups;

    /**
     * Authorization token for system users. Required to access /api/sys/* endpoints.
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $apiToken;

    #[ORM\ManyToOne]
    private ?AssetLicence $selectedLicence;

    public function __construct()
    {
        parent::__construct();
        $this->setApiToken(null);
        $this->setEnabled(true);
        $this->setAssetLicences(new ArrayCollection());
        $this->setAdminToExtSystems(new ArrayCollection());
        $this->setUserToExtSystems(new ArrayCollection());
        $this->setSelectedLicence(null);
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): static
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    public function getAuthId(): string
    {
        return (string) $this->getId();
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function hasNotRole(string $role): bool
    {
        return false === $this->hasRole($role);
    }

    public function addRole(string $role): self
    {
        if ($this->hasNotRole($role)) {
            $this->roles[] = $role;
        }

        return $this;
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
}
