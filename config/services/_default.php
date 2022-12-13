<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\String\Slugger\SluggerInterface;

return static function (ContainerConfigurator $configurator): void {
    $configurator->parameters()
        ->set('serializer_date_format', 'Y-m-d\\TH:i:s.u\\Z');

    $services = $configurator->services();

    $services
        ->defaults()
            ->autowire(true)
            ->autoconfigure(true)
    ;

    $services
        ->load('App\\', param('kernel.project_dir') . '/src/*')
        ->exclude([
            param('kernel.project_dir') . '/src/{Entity,Migrations,Model,Tests}',
            param('kernel.project_dir') . '/src/ApiFilter',
            param('kernel.project_dir') . '/src/Kernel.php',
        ])
    ;
};
