<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Repository\UserRepository;
use Symfony\Config\AnzuSystemsAuthConfig;

return static function (AnzuSystemsAuthConfig $config): void {
    $config
        ->cookie()
        ->domain(env('COOKIE_DOMAIN'))
        ->secure(env('COOKIE_SECURE')->bool())
    ;
    $config
        ->jwt()
        ->publicCert(env('AUTH_JWT_PUBLIC_CERT')->base64())
        ->privateCert(env('AUTH_JWT_PRIVATE_CERT')->base64())
    ;
    $authorizationConfig = $config->authorization();
    $authorizationConfig
        ->enabled(true)
        ->refreshToken()
            ->storage()
                ->redis()
                    ->serviceId('SharedTokenStorageRedis')
    ;
    $authorizationConfig
        ->authRedirectDefaultUrl(env('AUTH_REDIRECT_DEFAULT_URL'))
        ->authRedirectQueryUrlAllowedPattern(env('AUTH_REDIRECT_QUERY_URL_ALLOWED_PATTERN'))
    ;
    $authorizationConfig
        ->type('oauth2')
        ->oauth2()
        ->userRepositoryServiceId(UserRepository::class)
        ->stateTokenSalt(env('AUTH_OAUTH2_STATE_TOKEN_SALT'))
        ->authorizeUrl(env('AUTH_OAUTH2_AUTHORIZE_URL'))
        ->accessTokenUrl(env('AUTH_OAUTH2_ACCESS_TOKEN_URL'))
        ->redirectUrl(env('AUTH_OAUTH2_REDIRECT_URL'))
        ->userInfoByEmailUrl(env('AUTH_OAUTH2_USER_INFO_BY_EMAIL_URL'))
        ->userInfoUrl(env('AUTH_OAUTH2_USER_INFO_URL'))
//        ->clientId('anzusystems-dam')
        ->clientId('anzusystems-cms')
        ->clientSecret(env('AUTH_OAUTH2_CLIENT_SECRET'))
        ->publicCert(env('AUTH_OAUTH2_JWT_PUBLIC_CERT')->base64())
    ;
};
