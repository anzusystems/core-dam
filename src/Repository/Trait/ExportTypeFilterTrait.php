<?php

declare(strict_types=1);

namespace App\Repository\Trait;

use AnzuSystems\CoreDamBundle\Entity\PublicExport;
use AnzuSystems\CoreDamBundle\Model\Enum\ExportType;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;

trait ExportTypeFilterTrait
{
    protected function applyExportType(QueryBuilder $qb, PublicExport $publicExport, Order $order = Order::Descending): void
    {
        $this->applyExportTypeOrdering($qb, $publicExport, $order);
        $this->applyExportTypeEnable($qb, $publicExport);
    }

    protected function applyExportTypeOrdering(QueryBuilder $qb, PublicExport $publicExport, Order $order = Order::Descending): void
    {
        if ($publicExport->getType()->is(ExportType::Web)) {
            $qb->orderBy('entity.attributes.webOrderPosition', $order->value);

            return;
        }

        $qb->orderBy('entity.attributes.mobileOrderPosition', $order->value);
    }

    protected function applyExportTypeEnable(QueryBuilder $qb, PublicExport $publicExport, string $entityName = 'entity'): void
    {
        if ($publicExport->getType()->is(ExportType::Web)) {
            $qb->andWhere("{$entityName}.flags.webPublicExportEnabled = true");

            return;
        }

        $qb->andWhere("{$entityName}.flags.mobilePublicExportEnabled = true");
    }
}
