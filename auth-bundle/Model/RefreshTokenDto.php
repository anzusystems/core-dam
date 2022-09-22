<?php

declare(strict_types=1);

namespace AnzuSystems\AuthBundle\Model;

use DateTimeImmutable;

final class RefreshTokenDto
{
    public function __construct(
        private readonly string $tokenId,
        private readonly string $tokenHash,
        private readonly DeviceDto $device,
        private readonly DateTimeImmutable $expiresAt,
        private readonly DateTimeImmutable $issuedAt = new DateTimeImmutable(),
    ) {
    }

    public function getTokenId(): string
    {
        return $this->tokenId;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function getDevice(): DeviceDto
    {
        return $this->device;
    }

    public function getIssuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
