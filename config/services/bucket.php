<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Google\Cloud\Storage\StorageClient;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->defaults()
            ->autowire(true)
            ->autoconfigure(true);

    $services->set('anzu.google_storage.env_client')
        ->class(StorageClient::class)
        ->args([
            [
                'projectId' => 'anzu-devel-pp',
                'keyFilePath' => env('resolve:string:GOOGLE_BUCKET_CREDENTIALS')
            ]
        ])
    ;
};
