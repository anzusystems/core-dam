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

        $container->setParameter('anzu_systems.core_dam.artemis_audio_distribution', $processed['artemis_audio_distribution']);
        $container->setParameter('anzu_systems.core_dam.artemis_video_distribution', $processed['artemis_video_distribution']);
        $container->setParameter('anzu_systems.core_dam.asset_pub_configuration', $processed['asset_pub_configuration']);
        $container->setParameter('anzu_systems.core_dam.media_api_configuration', $processed['media_api']);
        $container->setParameter('anzu_systems.core_dam.rtmp', $processed['rtmp']);
    }
}
