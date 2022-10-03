<?php

declare(strict_types=1);

namespace App\Domain\RefreshToken;

use AnzuSystems\AuthBundle\Contracts\RefreshTokenStorageInterface;
use AnzuSystems\AuthBundle\Model\RefreshTokenDto;
use App\Entity\RefreshToken;
use App\Repository\RefreshTokenRepository;

final class RefreshTokenStorage implements RefreshTokenStorageInterface
{
    public function __construct(
        private readonly RefreshTokenRepository $refreshTokenRepo,
        private readonly RefreshTokenManager $refreshTokenManager,
    ) {
    }

    public function isValid(string $userId, string $deviceId, string $tokenHashPlain): bool
    {
        $refreshToken = $this->refreshTokenRepo->findByUserIdAndDeviceId($userId, $deviceId);
        if ($refreshToken instanceof RefreshToken) {
            return $refreshToken->isNotExpired() && password_verify($tokenHashPlain, $refreshToken->getTokenHash());
        }

        return false;
    }

    public function store(RefreshTokenDto $refreshTokenDto): void
    {
        $refreshToken = $this->refreshTokenRepo->findByUserIdAndDeviceId(
            $refreshTokenDto->getUserId(),
            $refreshTokenDto->getDevice()->getDeviceId()
        );
        if ($refreshToken instanceof RefreshToken) {
            $this->refreshTokenManager->updateFromDto($refreshToken, $refreshTokenDto);

            return;
        }

        $this->refreshTokenManager->createFromDto($refreshTokenDto);
    }

    public function invalidate(string $userId, string $deviceId): void
    {
        $refreshToken = $this->refreshTokenRepo->findByUserIdAndDeviceId($userId, $deviceId);
        if ($refreshToken instanceof RefreshToken) {
            $this->refreshTokenManager->invalidate($refreshToken);
        }
    }
}
