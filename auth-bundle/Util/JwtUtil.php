<?php

declare(strict_types=1);

namespace AnzuSystems\AuthBundle\Util;

use Anzu\AuthenticationBundle\Exception\MissingConfigurationException;
use AnzuSystems\AuthBundle\Configuration\CookieConfiguration;
use AnzuSystems\AuthBundle\Configuration\JwtConfiguration;
use DateTimeImmutable;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\RelatedTo;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;

final class JwtUtil
{
    public function __construct(
        private readonly JwtConfiguration $jwtConfiguration,
        private readonly CookieConfiguration $cookieConfiguration,
    ) {
    }

    /**
     * Can be used for creating a valid JWT. Useful especially for test environment.
     *
     * @throws MissingConfigurationException
     */
    public function create(string $userIdentifier, DateTimeImmutable $expiresAt = null): Plain
    {
        $privateCertificatePath = $this->jwtConfiguration->getPrivateCert();

        if (empty($privateCertificatePath)) {
            throw MissingConfigurationException::createForPrivateCertPath();
        }

        return (new Builder(new JoseEncoder(), ChainedFormatter::withUnixTimestampDates()))
            ->permittedFor($this->jwtConfiguration->getAudience())
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter(new DateTimeImmutable())
            ->expiresAt($expiresAt ?: DateTimeImmutable::createFromFormat('U', (string) ( time() + $this->cookieConfiguration->getJwtLifetime())))
            ->relatedTo($userIdentifier)
            ->getToken(
                $this->jwtConfiguration->getAlgorithm()->signer(),
                InMemory::plainText($privateCertificatePath)
            );
    }

    public function validate(Token $token): bool
    {
        $constraints = [
            new PermittedFor($this->jwtConfiguration->getAudience()),
            new RelatedTo($token->claims()->get(RegisteredClaims::SUBJECT)),
            new SignedWith(
                $this->jwtConfiguration->getAlgorithm()->signer(),
                InMemory::plainText($this->jwtConfiguration->getPublicCert())
            ),
        ];

        return (new Validator())->validate($token, ...$constraints);
    }
}
