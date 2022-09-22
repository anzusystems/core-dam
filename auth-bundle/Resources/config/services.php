<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use AnzuSystems\AuthBundle\Configuration\CookieConfiguration;
use AnzuSystems\AuthBundle\Configuration\JwtConfiguration;
use AnzuSystems\AuthBundle\Security\Authentication\JwtAuthentication;
use AnzuSystems\AuthBundle\Security\AuthenticationSuccessHandler;
use AnzuSystems\AuthBundle\Util\HttpUtil;
use AnzuSystems\AuthBundle\Util\JwtUtil;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(CookieConfiguration::class)
        ->arg('$domain', param('anzu_systems.auth_bundle.cookie.domain'))
        ->arg('$secure', param('anzu_systems.auth_bundle.cookie.secure'))
        ->arg('$jwtPayloadCookieName', param('anzu_systems.auth_bundle.cookie.jwt.payload_part_name'))
        ->arg('$jwtSignatureCookieName', param('anzu_systems.auth_bundle.cookie.jwt.signature_part_name'))
        ->arg('$jwtLifetime', param('anzu_systems.auth_bundle.cookie.jwt.lifetime'))
        ->arg('$refreshTokenCookieName', param('anzu_systems.auth_bundle.cookie.refresh_token.name'))
        ->arg('$refreshTokenLifetime', param('anzu_systems.auth_bundle.cookie.refresh_token.lifetime'))
    ;

    $services
        ->set(JwtConfiguration::class)
        ->arg('$audience', param('anzu_systems.auth_bundle.jwt.audience'))
        ->arg('$algorithm', param('anzu_systems.auth_bundle.jwt.algorithm'))
        ->arg('$publicCert', param('anzu_systems.auth_bundle.jwt.public_cert'))
        ->arg('$privateCert', param('anzu_systems.auth_bundle.jwt.private_cert'))
    ;

    $services
        ->set(JwtUtil::class)
        ->autowire()
    ;

    $services
        ->set(HttpUtil::class)
        ->autowire()
    ;

    $services
        ->set(JwtAuthentication::class)
        ->autowire()
    ;

    $services
        ->set(AuthenticationSuccessHandler::class)
        ->autowire()
    ;
};
