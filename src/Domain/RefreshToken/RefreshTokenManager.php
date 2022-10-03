<?php

declare(strict_types=1);

namespace App\Domain\RefreshToken;

use AnzuSystems\AuthBundle\Model\RefreshTokenDto;
use AnzuSystems\CommonBundle\Helper\PasswordHelper;
use AnzuSystems\CoreDamBundle\Domain\AbstractManager;
use App\Entity\RefreshToken;
use App\Entity\User;
use DateTimeImmutable;

/**
 * RefreshToken persistence management.
 */
final class RefreshTokenManager extends AbstractManager
{
    public function createFromDto(RefreshTokenDto $refreshTokenDto, bool $flush = true): RefreshToken
    {
        /** @var User $user */
        $user = $this->getEntityManager()->getReference(User::class, $refreshTokenDto->getUserId());
        $refreshToken = new RefreshToken();
        $refreshToken
            ->setUser($user)
            ->setTokenHash(PasswordHelper::passwordHash($refreshTokenDto->getTokenHashPlain()))
            ->setDeviceId($refreshTokenDto->getDevice()->getDeviceId())
            ->setIpAddress($refreshTokenDto->getDevice()->getIp())
            ->setDeviceInfo($refreshTokenDto->getDevice()->getInfo())
            ->setIssuedAt($refreshTokenDto->getIssuedAt())
            ->setExpiresAt($refreshTokenDto->getExpiresAt())
        ;
        $this->entityManager->persist($refreshToken);
        $this->flush($flush);

        return $refreshToken;
    }

    public function updateFromDto(
        RefreshToken $refreshToken,
        RefreshTokenDto $refreshTokenDto,
        bool $flush = true
    ): RefreshToken {
        $refreshToken
            ->setTokenHash(PasswordHelper::passwordHash($refreshTokenDto->getTokenHashPlain()))
            ->setIpAddress($refreshTokenDto->getDevice()->getIp())
            ->setDeviceInfo($refreshTokenDto->getDevice()->getInfo())
            ->setIssuedAt($refreshTokenDto->getIssuedAt())
            ->setExpiresAt($refreshTokenDto->getExpiresAt())
        ;
        $this->flush($flush);

        return $refreshToken;
    }

    public function invalidate(RefreshToken $refreshToken, bool $flush = true): RefreshToken
    {
        $refreshToken->setExpiresAt(new DateTimeImmutable());
        $this->flush($flush);

        return $refreshToken;
    }

    /**
     * Delete RefreshToken from persistence.
     */
    public function delete(RefreshToken $refreshToken, bool $flush = true): void
    {
        $this->entityManager->remove($refreshToken);
        $this->flush($flush);
    }
}
