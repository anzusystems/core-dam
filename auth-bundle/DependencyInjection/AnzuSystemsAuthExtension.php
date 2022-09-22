<?php

declare(strict_types=1);

namespace AnzuSystems\AuthBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class AnzuSystemsAuthExtension extends Extension
{
    /**
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $processedConfig = $this->processConfiguration(new Configuration(), $configs);

        $cookieSection = $processedConfig['cookie'];
        $container->setParameter('anzu_systems.auth_bundle.cookie.domain', $cookieSection['domain']);
        $container->setParameter('anzu_systems.auth_bundle.cookie.secure', $cookieSection['secure']);
        $container->setParameter('anzu_systems.auth_bundle.cookie.jwt.payload_part_name', $cookieSection['jwt']['payload_part_name']);
        $container->setParameter('anzu_systems.auth_bundle.cookie.jwt.signature_part_name', $cookieSection['jwt']['signature_part_name']);
        $container->setParameter('anzu_systems.auth_bundle.cookie.jwt.lifetime', $cookieSection['jwt']['lifetime']);
        $container->setParameter('anzu_systems.auth_bundle.cookie.refresh_token.name', $cookieSection['refresh_token']['name']);
        $container->setParameter('anzu_systems.auth_bundle.cookie.refresh_token.lifetime', $cookieSection['refresh_token']['lifetime']);

        $jwtSection = $processedConfig['jwt'];
        $container->setParameter('anzu_systems.auth_bundle.jwt.audience', $jwtSection['audience']);
        $container->setParameter('anzu_systems.auth_bundle.jwt.algorithm', $jwtSection['algorithm']);
        $container->setParameter('anzu_systems.auth_bundle.jwt.public_cert', $jwtSection['public_cert']);
        $container->setParameter('anzu_systems.auth_bundle.jwt.private_cert', $jwtSection['private_cert']);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');
    }
}
