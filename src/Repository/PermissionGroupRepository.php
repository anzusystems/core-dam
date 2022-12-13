<?php

declare(strict_types=1);

namespace App\Repository;

use AnzuSystems\CommonBundle\Repository\AbstractAnzuRepository;
use App\Entity\PermissionGroup;

/**
 * @extends AbstractAnzuRepository<PermissionGroup>
 *
 * @method PermissionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method PermissionGroup|null findOneBy(array $criteria, array $orderBy = null)
 */
final class PermissionGroupRepository extends AbstractAnzuRepository
{
    public function findOneByTitle(string $title): ?PermissionGroup
    {
        return $this->findOneBy([
            'title' => $title,
        ]);
    }

    protected function getEntityClass(): string
    {
        return PermissionGroup::class;
    }
}
