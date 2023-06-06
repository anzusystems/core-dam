<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $config): void {
    $config
        ->httpMethodOverride(true)
        ->secret(env('APP_SECRET'))
        ->trustedProxies(env('APP_TRUSTED_PROXIES'))
        ->trustedHeaders([
            'x-forwarded-for',
            'x-forwarded-port',
            'x-forwarded-proto',
        ])
    ;
    $config
        ->session()
            ->enabled(false)
    ;
    $config
        ->phpErrors()
            ->log(env('PHP_ERROR_REPORTING')->int())
    ;
    $config
        ->router()
            ->utf8(true)
            ->strictRequirements(null)
    ;
};
