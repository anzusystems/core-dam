<?php

declare(strict_types=1);

namespace Symfony\Component\Routing\Loader\Configurator;

use Symfony\Component\HttpFoundation\Request;

return static function (RoutingConfigurator $routes): void {
    $routes
        ->add('app.swagger', '/apidoc/json')
            ->methods([Request::METHOD_GET])
            ->controller('nelmio_api_doc.controller.swagger')
            ->condition("env('APP_DEPLOY_ENV') !== 'production'")
    ;

    $routes
        ->add('app.swagger_ui', '/apidoc/{area}')
            ->methods([Request::METHOD_GET])
            ->controller('nelmio_api_doc.controller.swagger_ui')
            ->defaults(['area' => 'default'])
            ->condition("env('APP_DEPLOY_ENV') !== 'production'")
    ;
};
