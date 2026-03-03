<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Model\UgcCookieConfiguration;
use App\Security\JWT\Signer\Key\EmptyInMemory;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();
    $configurator->parameters()
        ->set('empty_string', '')
    ;

    $services
        ->defaults()
            ->autowire()
            ->autoconfigure()
    ;

    $services
        ->set('anzu.security.ugc.verification_key', InMemory::class)
        ->factory([InMemory::class, 'base64Encoded'])
        ->arg('$contents', env('AUTH_UGC_JWT_PUBLIC_CERT'))
    ;
    $services
        ->set(Configuration::class . ' $jwtUgcConfiguration', Configuration::class)
        ->factory([Configuration::class, 'forAsymmetricSigner'])
        ->arg('$signer', inline_service(Sha256::class))
        ->arg('$signingKey', inline_service(EmptyInMemory::class))
        ->arg('$verificationKey', service('anzu.security.ugc.verification_key'))
        ->call('setValidationConstraints', [
            inline_service(PermittedFor::class)
                ->arg('$audience', 'sme_web'),
            inline_service(SignedWith::class)
                ->arg('$signer', inline_service(Sha256::class))
                ->arg('$key', service('anzu.security.ugc.verification_key')),
            inline_service(LooseValidAt::class)
                ->arg('$clock', inline_service()->factory([SystemClock::class, 'fromUTC']))
        ]);

    $services
        ->set('anzu.security.ugc_imp.verification_key', InMemory::class)
        ->factory([InMemory::class, 'base64Encoded'])
        ->arg('$contents', env('AUTH_UGC_IMP_JWT_PUBLIC_CERT'))
    ;
    $services
        ->set(Configuration::class . ' $jwtUgcImpConfiguration', Configuration::class)
        ->factory([Configuration::class, 'forAsymmetricSigner'])
        ->arg('$signer', inline_service(Sha256::class))
        ->arg('$signingKey', inline_service(EmptyInMemory::class))
        ->arg('$verificationKey', service('anzu.security.ugc_imp.verification_key'))
        ->call('setValidationConstraints', [
            inline_service(PermittedFor::class)
                ->arg('$audience', 'sme_web_imp'),
            inline_service(SignedWith::class)
                ->arg('$signer', inline_service(Sha256::class))
                ->arg('$key', service('anzu.security.ugc_imp.verification_key')),
            inline_service(LooseValidAt::class)
                ->arg('$clock', inline_service()->factory([SystemClock::class, 'fromUTC']))
        ]);

    $services
        ->set(UgcCookieConfiguration::class)
        ->arg('$jwtPayloadPartName', env('AUTH_UGC_JWT_PAYLOAD_PART_COOKIE_NAME'))
        ->arg('$jwtSignaturePartName', env('AUTH_UGC_JWT_SIGNATURE_PART_COOKIE_NAME'))
        ->arg('$jwtImpName', 'anz_imp')
    ;
};
