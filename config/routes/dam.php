<?php

declare(strict_types=1);

namespace Symfony\Component\Routing\Loader\Configurator;

return static function (RoutingConfigurator $routes): void {
    $routes
        ->import('@AnzuSystemsCoreDamBundle/Controller/Api/Adm/V1', type: 'attribute')
        ->prefix('/api/adm/v1/');

    $routes
        ->import('@AnzuSystemsCoreDamBundle/Controller/Api/Pub/V1', type: 'attribute')
        ->prefix('/api/pub/v1/');

    $routes
        ->import('@AnzuSystemsCoreDamBundle/Controller/Adm', type: 'attribute')
        ->prefix('/adm/');

    $routes
        ->import('@AnzuSystemsCoreDamBundle/Controller/ImageController.php', type: 'attribute')
        ->prefix('/');

    $routes
        ->import('@AnzuSystemsCoreDamBundle/Controller/YoutubeController.php', type: 'attribute')
        ->prefix('/');

    $routes
        ->import('@AnzuSystemsCoreDamBundle/Controller/AudioController.php', type: 'attribute')
        ->prefix('/');

    $routes
        ->import('@AnzuSystemsAuthBundle/Controller/Api/JsonCredentialsAuthController.php', type: 'attribute')
        ->prefix('/api/auth/');

    $routes
        ->import(__DIR__ . '/../../src/Controller/', type: 'attribute')
        ->prefix('/');

    $routes
        ->import(__DIR__ . '/../../src/Controller/Api/Adm/V1', type: 'attribute')
        ->prefix('/api/adm/v1/');
};
