<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\HttpClient\Sso\DefaultSsoUserClient;
use App\HttpClient\Sso\SsoUserClientInterface;
use Symfony\Component\Uid\Command\GenerateUuidCommand;

return static function (ContainerConfigurator $configurator): void {
    $configurator->parameters()
        ->set('serializer_date_format', 'Y-m-d\\TH:i:s.u\\Z')
        ->set('empty_string', '')
        ->set('tts_ext_system_slug_default', 'cms_tts')
    ;

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
            ->bind('$extSystemConfiguration', param('anzu_systems.core_dam.ext_systems_configuration'))
            ->bind('$exifCommonMetadata', param('anzu_systems.dam_bundle.common_metadata'))
            ->bind('$exifImageMetadata', param('anzu_systems.dam_bundle.image_metadata'))
            // TTS fixture bindings
            ->bind('$ttsExtSystemSlug', env('TTS_EXT_SYSTEM_SLUG')->default('tts_ext_system_slug_default')->string())
            ->bind('$defaultMaleVoiceId', env('ELEVENLABS_DEFAULT_MALE_VOICE_ID')->default('empty_string')->string())
            ->bind('$defaultFemaleVoiceId', env('ELEVENLABS_DEFAULT_FEMALE_VOICE_ID')->default('empty_string')->string())
    ;

    $services
        ->load('App\\', param('kernel.project_dir') . '/src/*')
        ->exclude([
            param('kernel.project_dir') . '/src/{Dev,Entity,Migrations,Model,Tests}',
            param('kernel.project_dir') . '/src/ApiFilter',
            param('kernel.project_dir') . '/src/Kernel.php',
        ])
    ;

    $services->alias(SsoUserClientInterface::class, DefaultSsoUserClient::class);

    $services->set(GenerateUuidCommand::class);
};
