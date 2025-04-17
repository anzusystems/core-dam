<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use AnzuSystems\AuthBundle\Security\Authentication\ApiTokenAuthenticator;
use AnzuSystems\AuthBundle\Security\Authentication\JwtAuthentication;
use AnzuSystems\Contracts\Entity\AnzuUser;
use App\Entity\User;
use App\Security\Authenticator\UgcAuthenticator;
use App\Security\Authenticator\UgcImpAuthenticator;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $config): void {
    $config
        ->provider('app_user_provider_id')
            ->entity()
                ->class(User::class)
                ->property('id')
    ;
    $config
        // Deprecated HIERARCHY
        ->roleHierarchy(AnzuUser::ROLE_ADMIN, [User::ROLE_DAM_ADMIN, User::ROLE_SYS_API, User::ROLE_UGC])
        ->roleHierarchy(User::ROLE_DAM_ADMIN, [AnzuUser::ROLE_USER])
        // ADM HIERARCHY
        ->roleHierarchy(User::ROLE_SUPER_ADMIN, [AnzuUser::ROLE_ADMIN, User::ROLE_SYS_API, User::ROLE_UGC])
        ->roleHierarchy(AnzuUser::ROLE_ADMIN, [AnzuUser::ROLE_USER])
        // UGC hierarchy
        ->roleHierarchy(User::ROLE_UGC, [AnzuUser::ROLE_USER])
        // SYS hierarchy
        ->roleHierarchy(User::ROLE_SYS_API, [User::ROLE_SYS_JOB_API, User::ROLE_SYS_ARTEMIS_API])
        ->roleHierarchy(User::ROLE_SYS_JOB_API, null)
        ->roleHierarchy(User::ROLE_SYS_ARTEMIS_API, null)
        // Base user ROLE
        ->roleHierarchy(AnzuUser::ROLE_USER, null)
    ;
    $config->passwordHasher(User::class, 'auto');
    $config
        ->firewall('dev')
            ->pattern('^/(_(profiler|wdt)|css|images|js)/')
            ->security(false)
    ;
    $config
        ->firewall('pub')
        ->pattern('^/api/pub/')
        ->security(false)
    ;
    $config
        ->firewall('auth')
            ->pattern('^/api/auth/')
            ->security(false)
            ->stateless(true)
            ->logout()
                ->path('auth_logout')
    ;
    $config
        ->firewall('sys')
            ->pattern('^/api/sys/')
            ->stateless(true)
            ->provider('app_user_provider_id')
            ->customAuthenticators([
                ApiTokenAuthenticator::class,
            ])
    ;
    $config
        ->firewall('adm')
            ->pattern('^/api/adm/')
            ->stateless(true)
            ->provider('app_user_provider_id')
            ->customAuthenticators([
                JwtAuthentication::class,
            ])
    ;
    $config
        ->firewall('ugc')
        ->pattern('^/api/ugc/')
        ->stateless(true)
        ->provider('app_user_provider_id')
        ->entryPoint(UgcAuthenticator::class)
        ->customAuthenticators([
            UgcAuthenticator::class,
            UgcImpAuthenticator::class,
        ])
    ;
    $config->accessControl()->path('^/api/pub/')->roles(['PUBLIC_ACCESS']);
    $config->accessControl()->path('^/api/auth/')->roles(['PUBLIC_ACCESS']);
    $config->accessControl()->path('^/api/adm/')->roles([User::ROLE_DAM_ADMIN, AnzuUser::ROLE_ADMIN]);
    $config->accessControl()->path('^/api/ugc/')->roles([User::ROLE_UGC]);
    $config->accessControl()->path('^/adm/')->roles([User::ROLE_DAM_ADMIN, AnzuUser::ROLE_ADMIN]);
    $config->accessControl()->path('^/api/sys/v(\d+)/job')->roles([User::ROLE_SYS_JOB_API]);
    $config->accessControl()->path('^/api/sys/v(\d+)/mediaapi')->roles([User::ROLE_SYS_MEDIAAPI_API]);
    $config->accessControl()->path('^/api/sys/v(\d+)/image/copy-to-licence')->roles([User::ROLE_SYS_COPY_IMAGE_API]);
    $config->accessControl()->path('^/api/sys/')->roles([User::ROLE_SYS_API]);
};
