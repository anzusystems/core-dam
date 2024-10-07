<?php

declare(strict_types=1);

namespace App\Repository\CustomFilter;

use AnzuSystems\CommonBundle\ApiFilter\CustomFilterInterface;
use Doctrine\ORM\QueryBuilder;

final class AnzuUserPermissionGroupFilter implements CustomFilterInterface
{
    private const string FILTER_NAME = 'permissionGroups';

    public function apply(QueryBuilder $dqb, string $field, int|string $value): QueryBuilder
    {
        if (self::FILTER_NAME === $field && false === empty($value)) {
            $items = array_map(
                static fn (string $id): int => (int) trim($id),
                explode(',', (string) $value)
            );

            $dqb
                ->innerJoin('t.permissionGroups', 'pg')
                ->andWhere('pg.id in (:ids)')
                ->setParameter('ids', $items)
            ;
        }

        return $dqb;
    }
}
