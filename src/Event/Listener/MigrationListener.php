<?php

declare(strict_types=1);

namespace App\Event\Listener;

use AnzuSystems\CommonBundle\Traits\EntityManagerAwareTrait;
use Doctrine\Migrations\Event\MigrationsEventArgs;
use Doctrine\Migrations\Events;
use Doctrine\ORM\Cache;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: Events::onMigrationsMigrated)]
final class MigrationListener
{
    use EntityManagerAwareTrait;

    public function __invoke(MigrationsEventArgs $args): void
    {
        $cache = $this->entityManager->getCache();
        if ($cache instanceof Cache) {
            $cache->evictQueryRegions();
            $cache->evictEntityRegions();
            $cache->evictCollectionRegions();
        }
    }
}
