<?php

declare(strict_types=1);

namespace App\Entity;

use AnzuSystems\Contracts\Entity\Interfaces\UuidIdentifiableInterface;
use AnzuSystems\CoreDamBundle\App;
use AnzuSystems\CoreDamBundle\Entity\Traits\UuidIdentityTrait;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use App\Repository\RefreshTokenRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
#[ORM\UniqueConstraint(fields: ['user', 'deviceId'])]
class RefreshToken implements UuidIdentifiableInterface
{
    use UuidIdentityTrait;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn]
    #[Serialize(handler: EntityIdHandler::class)]
    private User $user;

    #[ORM\Column(type: Types::STRING, length: 36)]
    #[Serialize]
    private string $deviceId;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Serialize]
    private string $tokenHash;

    #[ORM\Column(type: Types::STRING, length: 45)]
    #[Serialize]
    private string $ipAddress;

    #[ORM\Column(type: Types::JSON)]
    #[Serialize(type: Serialize::KEYS_VALUES)]
    private array $deviceInfo;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Serialize]
    private DateTimeImmutable $issuedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Serialize]
    private DateTimeImmutable $expiresAt;

    public function __construct()
    {
        $this->setUser(new User());
        $this->setDeviceId('');
        $this->setTokenHash('');
        $this->setIpAddress('');
        $this->setDeviceInfo([]);
        $this->setIssuedAt(App::getAppDate());
        $this->setExpiresAt(App::getAppDate());
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function setDeviceId(string $deviceId): self
    {
        $this->deviceId = $deviceId;

        return $this;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function setTokenHash(string $tokenHash): self
    {
        $this->tokenHash = $tokenHash;

        return $this;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getDeviceInfo(): array
    {
        return $this->deviceInfo;
    }

    public function setDeviceInfo(array $deviceInfo): self
    {
        $this->deviceInfo = $deviceInfo;

        return $this;
    }

    public function getIssuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function setIssuedAt(DateTimeImmutable $issuedAt): self
    {
        $this->issuedAt = $issuedAt;

        return $this;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function isExpired(): bool
    {
        return $this->getExpiresAt() < new DateTimeImmutable();
    }

    public function isNotExpired(): bool
    {
        return false === $this->isExpired();
    }
}
