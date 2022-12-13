<?php

declare(strict_types=1);

namespace App\Repository;

use AnzuSystems\AuthBundle\Contracts\AnzuAuthUserInterface;
use AnzuSystems\AuthBundle\Contracts\OAuth2AuthUserRepositoryInterface;
use AnzuSystems\CommonBundle\Repository\AbstractAnzuRepository;
use App\Entity\User;

/**
 * @extends AbstractAnzuRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 */
final class UserRepository extends AbstractAnzuRepository
{
    protected function getEntityClass(): string
    {
        return User::class;
    }
}
