<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Uid\Command\GenerateUuidCommand;

return static function (ContainerConfigurator $configurator): void {
    $configurator->parameters()
        ->set('serializer_date_format', 'Y-m-d\\TH:i:s.u\\Z')
        ->set('empty_string', '');

    $services = $configurator->services();

    $services
        ->defaults()
            ->autowire()
            ->autoconfigure()
            ->bind('$privateUgcCert', env('AUTH_UGC_JWT_PRIVATE_CERT')->default('empty_string')->base64())
    ;

    $services
        ->load('App\\', param('kernel.project_dir') . '/src/*')
        ->exclude([
            param('kernel.project_dir') . '/src/{Entity,Migrations,Model,Tests}',
            param('kernel.project_dir') . '/src/ApiFilter',
            param('kernel.project_dir') . '/src/Kernel.php',
        ])
    ;

    $services->set(GenerateUuidCommand::class);
};
