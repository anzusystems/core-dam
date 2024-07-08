<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use AnzuSystems\Contracts\Entity\AnzuUser;
use AnzuSystems\CoreDamBundle\Entity\DamUser;
use App\Entity\User;
use Symfony\Config\DoctrineConfig;

return static function (DoctrineConfig $config): void {
    $dbalConfig = $config->dbal();
    $dbalConfig
        ->connection('default')
        ->url(env('DB_CORE_DAM_URL')->resolve())
    ;
    $dbalConfig
        ->connection('media_api')
        ->url(env('DB_MEDIA_API_URL')->resolve())
    ;
    $dbalConfig
        ->connection('dam_media_api_mig')
        ->url(env('DB_DAM_MEDIA_API_MIG_NAME_URL')->resolve())
    ;
    $ormConfig = $config->orm();
    $ormConfig
        ->enableLazyGhostObjects(true)
        ->defaultEntityManager('default')
        ->autoGenerateProxyClasses(true)
        ->resolveTargetEntity(AnzuUser::class,  User::class)
        ->resolveTargetEntity(DamUser::class,  User::class)
    ;
    $ormDefaultEntityManagerConfig = $ormConfig->entityManager('default');
    $ormDefaultEntityManagerConfig
        ->mapping('App')
        ->isBundle(false)
        ->type('attribute')
        ->dir(param('kernel.project_dir') . '/src/Entity')
        ->prefix('App\Entity')
        ->alias('App')
    ;
    $ormDefaultEntityManagerConfig
        ->mapping('AnzuSystemsCoreDamBundle')
        ->isBundle(true)
        ->type('attribute')
        ->dir('Entity')
        ->prefix('AnzuSystems\CoreDamBundle\Entity')
        ->alias('AnzuSystems\CoreDamBundle')
    ;
    $ormDefaultEntityManagerConfig
        ->mapping('AnzuSystemsCommonBundle')
        ->isBundle(true)
        ->type('attribute')
        ->dir('Entity')
        ->prefix('AnzuSystems\CommonBundle\Entity')
        ->alias('AnzuSystems\CommonBundle')
    ;
    $ormDefaultEntityManagerConfig
        ->mapping('AnzuSystemsContractsEmbeds')
        ->isBundle(false)
        ->type('attribute')
        ->dir(param('kernel.project_dir') . '/vendor/anzusystems/contracts/src/Entity')
        ->prefix('AnzuSystems\Contracts\Entity')
        ->alias('AnzuSystems\Contracts')
    ;
    $ormDefaultEntityManagerConfig
        ->namingStrategy('doctrine.orm.naming_strategy.underscore_number_aware')
        ->autoMapping(true)
    ;
    $ormDefaultEntityManagerConfig
        ->secondLevelCache()
        ->enabled(env('DOCTRINE_CACHE_ENABLED')->bool())
        ->logEnabled(false)
        ->regionLifetime(env('DOCTRINE_CACHE_TTL')->int())
        ->regionCacheDriver()
            ->pool('doctrine.redis_cache_pool')
    ;
    $ormDefaultEntityManagerConfig
        ->metadataCacheDriver()
            ->type('pool')
            ->pool('cache.system')
    ;
    $ormDefaultEntityManagerConfig
        ->queryCacheDriver()
            ->type('pool')
            ->pool('cache.system')
    ;
    $ormDefaultEntityManagerConfig
        ->resultCacheDriver()
            ->type('pool')
            ->pool('cache.system')
    ;
};
