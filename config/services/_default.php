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
            ->bind('$artemisAudioDistribution', param('anzu_systems.core_dam.artemis_audio_distribution'))
            ->bind('$artemisVideoDistribution', param('anzu_systems.core_dam.artemis_video_distribution'))
            ->bind('$rtmp', param('anzu_systems.core_dam.rtmp'))
            ->bind('$privateUgcCert', env('AUTH_UGC_JWT_PRIVATE_CERT')->default('empty_string')->base64())
            ->bind('$cachePurgeUrl', env('CORE_DAM_CACHE_PURGE_URL')->default('empty_string'))
            ->bind('$cacheProxyPurgeEnabled', env('bool:CORE_DAM_CACHE_PROXY_PURGE_ENABLED'))
            ->bind('$cdnPurgeEnabled', env('bool:CORE_DAM_CDN_PURGE_ENABLED'))
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
