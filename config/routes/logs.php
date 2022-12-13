<?php

declare(strict_types=1);

namespace Symfony\Component\Routing\Loader\Configurator;

use AnzuSystems\CommonBundle\Controller\LogController;
use Symfony\Component\HttpFoundation\Request;

return static function (RoutingConfigurator $routes): void {
    $routes
        ->add('anzu_common.logs.app_list', '/api/adm/v1/log/app')
            ->methods([Request::METHOD_GET])
            ->controller([LogController::class, 'getAppLogs'])
    ;

    $routes
        ->add('anzu_common.logs.audit_list', '/api/adm/v1/log/audit')
            ->methods([Request::METHOD_GET])
            ->controller([LogController::class, 'getAuditLogs'])
    ;

    $routes
        ->add('anzu_common.logs.app_get_one', '/api/adm/v1/log/app/{id}')
            ->methods([Request::METHOD_GET])
            ->controller([LogController::class, 'getOneAppLog'])
    ;

    $routes
        ->add('anzu_common.logs.audit_get_one', '/api/adm/v1/log/audit/{id}')
            ->methods([Request::METHOD_GET])
            ->controller([LogController::class, 'getOneAuditLog'])
    ;

    $routes
        ->add('anzu_common.logs.create', '/api/adm/v1/log')
            ->methods([Request::METHOD_POST])
            ->controller([LogController::class, 'create'])
    ;
};
