<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Google\Cloud\Storage\StorageClient;

return static function (ContainerConfigurator $configurator): void {
    $configurator->parameters()
        ->set('empty_array', []);

    $services = $configurator->services();

    $services
        ->defaults()
            ->autowire()
            ->autoconfigure();

    $services->set('anzu.google_storage.env_client', StorageClient::class)
        ->args([
            [
                'projectId' => 'anzu-devel-pp', // TODO why it's hardcoded?
                'keyFilePath' => env('GOOGLE_BUCKET_CREDENTIALS')->resolve()->string()
            ]
        ])
    ;

    $services->set('anzu.google_storage.env_fallback_client')
        ->class(StorageClient::class)
        ->lazy()
        ->args([
            [
                'projectId' => 'anzu-devel-pp',
                'keyFile' => env('file:GOOGLE_FALLBACK_BUCKET_CREDENTIALS')->json()->default('empty_array')
            ]
        ])
    ;
};
