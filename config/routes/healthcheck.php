<?php

declare(strict_types=1);

namespace Symfony\Component\Routing\Loader\Configurator;

use AnzuSystems\CommonBundle\Controller\LogController;
use Symfony\Component\HttpFoundation\Request;
use  AnzuSystems\CommonBundle\Controller\HealthCheckController;

return static function (RoutingConfigurator $routes): void {
    // todo move to SYS
    $routes
        ->add('anzu_systems_common.health_ceck', '/api/pub/v1/health')
            ->methods([Request::METHOD_GET])
            ->controller([HealthCheckController::class, 'healthCheck'])
    ;
};
