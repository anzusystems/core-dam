<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use AnzuSystems\CoreDamBundle\Domain\Tts\Config;
use AnzuSystems\CoreDamBundle\Domain\Tts\HttpClient\ElevenlabsClient;
use AnzuSystems\CoreDamBundle\Domain\Tts\Provider\ElevenlabsTtsProvider;
use AnzuSystems\CoreDamBundle\Domain\Tts\Provider\GoogleTtsProvider;

return static function (ContainerConfigurator $configurator): void {
    // Default-fallback parameters for TTS env vars. Symfony's `default:<fallback>:<ENV>` resolves
    // <fallback> as a *parameter name*, not a literal — so the literal defaults must live as
    // dedicated parameters and the env() chain references them by name.
    $configurator->parameters()
        ->set('tts_active_provider_default', 'elevenlabs')
        ->set('tts_system_default_family_slug_default', 'sme_default_male')
        ->set('tts_chunk_size_chars_default', '4800')
        ->set('tts_master_slot_name_default', 'premium')
        ->set('tts_preview_slot_name_default', 'free')
        ->set('tts_default_language_code', 'sk-SK')
    ;

    $services = $configurator->services();

    $services
        ->defaults()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(Config::class)
        ->args([
            env('TTS_ACTIVE_PROVIDER')->default('tts_active_provider_default')->string(),
            env('TTS_SYSTEM_DEFAULT_FAMILY_SLUG')->default('tts_system_default_family_slug_default')->string(),
            env('TTS_CHUNK_SIZE_CHARS')->default('tts_chunk_size_chars_default')->int(),
            env('TTS_MASTER_SLOT_NAME')->default('tts_master_slot_name_default')->string(),
            env('TTS_PREVIEW_SLOT_NAME')->default('tts_preview_slot_name_default')->string(),
        ])
    ;

    // ElevenlabsClient + GoogleTtsProvider resolve per-tenant credentials at synthesis time via
    // ExtSystemConfigurationProvider — see anzu_systems_core_dam.ext_systems.<slug>.tts in YAML.
    $services->set(ElevenlabsClient::class);
    $services->set(ElevenlabsTtsProvider::class);
    $services->set(GoogleTtsProvider::class);
};
