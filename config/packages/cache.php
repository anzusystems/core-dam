<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $config): void {
    $cacheConfig = $config->cache();
    $cacheConfig
        ->defaultRedisProvider('VersionedCacheRedis')
        ->app('core_dam_app.cache')
        ->system('cache.adapter.filesystem')
    ;
    $cacheConfig
        ->pool('doctrine.redis_cache_pool')
            ->adapters(['cache.adapter.redis'])
            ->provider('CacheRedis')
            ->defaultLifetime('PT3M')
    ;
    $cacheConfig
        ->pool('core_dam_app.cache')
            ->adapters(['cache.adapter.redis'])
            ->provider('DamRedis')
            ->defaultLifetime('PT3M')
    ;
};
