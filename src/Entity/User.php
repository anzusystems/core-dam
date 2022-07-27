<?php

declare(strict_types=1);

namespace App\Entity;

use Anzu\AuthenticationBundle\Contracts\SsoUserInterface;
use Anzu\CommonBundle\AnzuSerializer\Attributes\AnzuSerialize;
use Anzu\CommonBundle\Entity\AnzuUser;
use Anzu\CommonBundle\Entity\Interfaces\IdentifiableInterface;
use Anzu\CommonBundle\Entity\Interfaces\TimeTrackingInterface;
use Anzu\CommonBundle\Entity\Interfaces\UserTrackingInterface;
use Anzu\CommonBundle\Entity\Traits\TimeTrackingTrait;
use Anzu\CommonBundle\Exception\ValidationException;
use App\Entity\Traits\UserTrackingTrait;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_email', fields: ['email'])]
class User extends AnzuUser implements IdentifiableInterface, TimeTrackingInterface, Stringable, SsoUserInterface, UserTrackingInterface
{
    use TimeTrackingTrait;
    use UserTrackingTrait;

    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_SYS_API = 'ROLE_SYS_API';

    public const ID_ANONYMOUS = 1_763_600;
    public const ID_CONSOLE = 1_000_000;

    public const SYSTEM_USER_IDS = [
        self::ID_ANONYMOUS,
        self::ID_CONSOLE,
    ];

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 180, maxMessage: ValidationException::ERROR_FIELD_LENGTH_MAX)]
    private string $email;

    #[ORM\Column(type: Types::STRING, length: 120)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120, maxMessage: ValidationException::ERROR_FIELD_LENGTH_MAX)]
    #[AnzuSerialize]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 120)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120, maxMessage: ValidationException::ERROR_FIELD_LENGTH_MAX)]
    #[AnzuSerialize]
    private string $surname;

    /**
     * List of permissions which belongs to user.
     *
     * @var array<string, int>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $permissions;

    /**
     * Authorization token for system users. Required to access /api/sys/* endpoints.
     *
     * @Serializer\Exclude()
     */
    #[ORM\Column(unique: true, nullable: true)]
    private ?string $apiToken = null;

    public function __construct()
    {
        $this->setEnabled(true);
        $this->setEmail('');
        $this->setName('');
        $this->setSurname('');
        $this->setRoles([]);
        $this->setPermissions([]);
    }

    public function __toString(): string
    {
        return $this->getEmail();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $apiToken): self
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->getEmail();
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function setPermissions(array $permissions): self
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }
}
