<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

final class CoreDamExtension extends Extension
{
    /**
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $processed = $this->processConfiguration($configuration, $configs);

        $container->setParameter('anzu_systems.core_dam.audio_processed_automat_configuration', $processed['cms_audio_processed_automat']);
        $container->setParameter('anzu_systems.core_dam.asset_pub_configuration', $processed['asset_pub_configuration']);
        $container->setParameter('anzu_systems.core_dam.media_api_configuration', $processed['media_api']);
        $container->setParameter('anzu_systems.core_dam.rtmp', $processed['rtmp']);
        $container->setParameter('anzu_systems.core_dam.ext_systems_configuration', $processed['ext_systems']);
    }
}
