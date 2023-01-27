<?php

declare(strict_types=1);

namespace App\Security\Util;

use App\Entity\User;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Plain;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class JwtUgcUtil
{
    public function __construct(
        private Configuration $jwtUgcConfiguration,
        private string $privateUgcCert,
    ) {
    }

    /**
     * @throws AccessDeniedException
     */
    public function createForUser(User $user): Plain
    {
        if (empty($this->privateUgcCert)) {
            throw new AccessDeniedException('Missing private certificate to sign a token');
        }

        $builder = $this->jwtUgcConfiguration
            ->builder()
            ->permittedFor('sme_web')
            ->identifiedBy(uuid_create())
            ->issuedAt(new DateTimeImmutable())
            ->expiresAt(new DateTimeImmutable('+1 year'))
            ->relatedTo((string) $user->getId())
            ->withClaim('eml', $user->getEmail())
            ->withClaim('cnf', true) // confirmed user
        ;

        return $builder->getToken(
            $this->jwtUgcConfiguration->signer(),
            InMemory::plainText($this->privateUgcCert)
        );
    }
}
