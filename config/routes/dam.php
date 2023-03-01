<?php

declare(strict_types=1);

namespace Symfony\Component\Routing\Loader\Configurator;

use App\Controller\Api\Sys\V1\JobController;
use Symfony\Component\HttpFoundation\Request;

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
        ->import('@AnzuSystemsAuthBundle/Controller/Api/OAuth2AuthController.php', type: 'attribute')
        ->prefix('/api/auth/');

    $routes
        ->import(__DIR__ . '/../../src/Controller/', type: 'attribute')
        ->prefix('/');

    $routes
        ->import(__DIR__ . '/../../src/Controller/Api/Adm/V1', type: 'attribute')
        ->prefix('/api/adm/v1/');

    $routes
        ->import(__DIR__ . '/../../src/Controller/Api/Ugc/VLegacy', type: 'attribute')
        ->prefix('/api/ugc/vlegacy/');

    $routes
        ->import(__DIR__ . '/../../src/Controller/Api/Sys/V1', type: 'attribute')
        ->prefix('/api/sys/v1/');

    $routes
        ->add('anzu_core.job.legacy_gdpr', '/api/sys/v1/job/gdpr-delete')
        ->methods([Request::METHOD_POST])
        ->controller([JobController::class, 'createUserDataDelete'])
    ;
};
