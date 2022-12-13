<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Redis;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->defaults()
            ->autowire(true)
            ->autoconfigure(true)
    ;

    $services
        ->set('DamRedis', Redis::class)
        ->call('connect', [
            env('string:REDIS_HOST'),
            env('int:REDIS_PORT'),
        ])
        ->call('select', [
            env('int:REDIS_DB')
        ])
        ->call('setOption', [
            Redis::OPT_PREFIX,
            'core_dam_' . env('string:APP_ENV') . '_',
        ])
    ;

    $services
        ->set('VersionedCacheRedis', Redis::class)
        ->call('connect', [
            env('string:REDIS_CACHE_HOST'),
            env('int:REDIS_CACHE_PORT'),
        ])
        ->call('select', [
            env('int:REDIS_CACHE_DB')
        ])
        ->call('setOption', [
            Redis::OPT_PREFIX,
            'core_dam_cache_' . env('string:APP_ENV') . '_' . env('string:APP_VERSION') . '_',
        ])
    ;

    $services
        ->set('CacheRedis', Redis::class)
        ->call('connect', [
            env('string:REDIS_CACHE_HOST'),
            env('int:REDIS_CACHE_PORT'),
        ])
        ->call('select', [
            env('int:REDIS_CACHE_DB')
        ])
        ->call('setOption', [
            Redis::OPT_PREFIX,
            'core_dam_cache_' . env('string:APP_ENV') . '_',
        ])
    ;

    $services
        ->set('SharedTokenStorageRedis', Redis::class)
        ->call('connect', [
            env('string:REDIS_HOST'),
            env('int:REDIS_PORT'),
        ])
        ->call('select', [0])
    ;

    $services
        ->set(Cache::class)
        ->factory([DoctrineProvider::class, 'wrap'])
        ->arg('$pool', service('doctrine.redis_cache_pool'))
    ;
};
