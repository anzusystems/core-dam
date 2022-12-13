<?php

declare(strict_types=1);

namespace App\Repository;

use AnzuSystems\CommonBundle\Repository\AbstractAnzuRepository;
use App\Entity\RefreshToken;

/**
 * @extends AbstractAnzuRepository<RefreshToken>
 *
 * @method RefreshToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method RefreshToken|null findOneBy(array $criteria, array $orderBy = null)
 */
final class RefreshTokenRepository extends AbstractAnzuRepository
{
    public function findByUserIdAndDeviceId(string $userId, string $deviceId): ?RefreshToken
    {
        return $this->createQueryBuilder('refreshToken')
            ->where('IDENTITY(refreshToken.user) = :userId')
            ->andWhere('refreshToken.deviceId = :deviceId')
            ->setParameter('userId', $userId)
            ->setParameter('deviceId', $deviceId)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    protected function getEntityClass(): string
    {
        return RefreshToken::class;
    }
}
