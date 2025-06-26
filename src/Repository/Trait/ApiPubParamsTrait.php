<?php

declare(strict_types=1);

namespace App\Repository\Trait;

use App\Model\Request\ApiPubParams;
use Doctrine\ORM\QueryBuilder;

trait ApiPubParamsTrait
{
    protected function applyApiPubParams(QueryBuilder $qb, ApiPubParams $apiParams): void
    {
        $qb->setMaxResults($apiParams->getLimit() + 1)
            ->setFirstResult(($apiParams->getPage() * $apiParams->getLimit()) - $apiParams->getLimit())
        ;

        if (false === empty($apiParams->getExcludeIds())) {
            $qb->andWhere('entity.id NOT IN (:excludeIds)')
                ->setParameter('excludeIds', $apiParams->getExcludeIds())
            ;
        }
    }
}
