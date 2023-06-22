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
use AnzuSystems\CoreDamBundle\Entity\Traits\UserTrackingTrait;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_email', fields: ['email'])]
class User extends DamUser implements
    AnzuAuthUserInterface,
    PasswordAuthenticatedUserInterface,
    ApiTokenUserInterface,
    UserTrackingInterface,
    TimeTrackingInterface
{
    use UserTrackingTrait;
    use TimeTrackingTrait;

    public const ID_ANONYMOUS = 1;
    public const ID_CONSOLE = 2;
    public const ID_ADMIN = 3;
    public const ID_BASIC_USER = 4;

    /**
     * Authorization password for system users.
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $password;

    /**
     * Authorization token for system users. Required to access /api/sys/* endpoints.
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $apiToken;

    #[ORM\ManyToMany(targetEntity: PermissionGroup::class, inversedBy: 'users', fetch: App::DOCTRINE_EXTRA_LAZY, indexBy: 'id')]
    #[ORM\JoinTable]
    #[Serialize(handler: EntityIdHandler::class, type: PermissionGroup::class)]
    protected Collection $permissionGroups;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?AssetLicence $selectedLicence;

    public function __construct()
    {
        parent::__construct();
        $this->setPermissions([]);
        $this->setApiToken(null);
        $this->setPassword(null);
        $this->setEnabled(true);
        $this->setRoles([self::ROLE_USER]);
        $this->setPermissionGroups(new ArrayCollection());
        $this->setAssetLicences(new ArrayCollection());
        $this->setAdminToExtSystems(new ArrayCollection());
        $this->setUserToExtSystems(new ArrayCollection());
        $this->setSelectedLicence(null);
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
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

    public function addRole(string $role): self
    {
        if (false === $this->hasRole($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(string $role): self
    {
        $foundKey = array_search($role, $this->roles, true);
        if (is_int($foundKey)) {
            unset($this->roles[$foundKey]);
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
