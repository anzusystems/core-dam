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
            ->bind('$cmsAudioProcessedAutomat', param('anzu_systems.core_dam.audio_processed_automat_configuration'))
            ->bind('$assetPubConfigurationData', param('anzu_systems.core_dam.asset_pub_configuration'))
            ->bind('$mediaApiConfiguration', param('anzu_systems.core_dam.media_api_configuration'))
            ->bind('$rtmp', param('anzu_systems.core_dam.rtmp'))
            ->bind('$privateUgcCert', env('AUTH_UGC_JWT_PRIVATE_CERT')->default('empty_string')->base64())
            ->bind('$cachePurgeUrl', env('CORE_DAM_CACHE_PURGE_URL')->default('empty_string'))
            ->bind('$cacheProxyPurgeEnabled', env('bool:CORE_DAM_CACHE_PROXY_PURGE_ENABLED'))
            ->bind('$cdnPurgeEnabled', env('bool:CORE_DAM_CDN_PURGE_ENABLED'))
            ->bind('$exifCommonMetadata', param('anzu_systems.dam_bundle.common_metadata'))
            ->bind('$exifImageMetadata', param('anzu_systems.dam_bundle.image_metadata'))
            ->bind('$zoneId', env('CLOUD_FLARE_API_ZONE')->default('empty_string'))
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
