<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Redis;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->defaults()
            ->autowire()
            ->autoconfigure()
    ;

    $services
        ->set('DamRedis', Redis::class)
        ->call('connect', [
            env('REDIS_HOST')->string(),
            env('REDIS_PORT')->int(),
        ])
        ->call('select', [
            env('REDIS_DB')->int()
        ])
        ->call('setOption', [
            Redis::OPT_PREFIX,
            'core_dam_' . env('APP_ENV')->string() . '_',
        ])
        ->lazy()
    ;

    $services
        ->set('VersionedCacheRedis', Redis::class)
        ->call('connect', [
            env('REDIS_CACHE_HOST')->string(),
            env('REDIS_CACHE_PORT')->int(),
        ])
        ->call('select', [
            env('REDIS_CACHE_DB')->int()
        ])
        ->call('setOption', [
            Redis::OPT_PREFIX,
            'core_dam_cache_' . env('APP_ENV')->string() . '_' . env('APP_VERSION')->string() . '_',
        ])
        ->lazy()
    ;

    $services
        ->set('CacheRedis', Redis::class)
        ->call('connect', [
            env('REDIS_CACHE_HOST')->string(),
            env('REDIS_CACHE_PORT')->int(),
        ])
        ->call('select', [
            env('REDIS_CACHE_DB')->int()
        ])
        ->call('setOption', [
            Redis::OPT_PREFIX,
            'core_dam_cache_' . env('APP_ENV')->string() . '_',
        ])
        ->lazy()
    ;

    $services
        ->set('SharedTokenStorageRedis', Redis::class)
        ->call('connect', [
            env('REDIS_HOST')->string(),
            env('REDIS_PORT')->int(),
        ])
        ->call('select', [0])
        ->lazy()
    ;
};
