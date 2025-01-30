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
        if ($publicExport->getType()->is(ExportType::Web)) {
            $qb
                ->orderBy('entity.attributes.webOrderPosition', $order->value)
                ->andWhere('entity.flags.webPublicExportEnabled = true');
            return;
        }

        $qb
            ->orderBy('entity.attributes.mobileOrderPosition', $order->value)
            ->andWhere('entity.flags.mobilePublicExportEnabled = true');
    }
}
