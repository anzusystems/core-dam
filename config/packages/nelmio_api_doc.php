<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\NelmioApiDocConfig;

return static function (NelmioApiDocConfig $config): void {
    $config
        ->documentation('openapi', '3.0.0')
        ->documentation('info', [
            'title' => 'Anzu CoreDam',
            'description' => 'CoreDam microservice',
            'version' => env('APP_VERSION')->string(),
        ])
    ;
    $config
        ->areas('default')
            ->pathPatterns([
                '^/api/',
            ])
    ;
    $config
        ->areas('adm')
            ->pathPatterns([
                '^/api/adm/',
            ])
    ;
    $config
        ->areas('sys')
            ->pathPatterns([
                '^/api/sys/',
            ])
    ;
};
