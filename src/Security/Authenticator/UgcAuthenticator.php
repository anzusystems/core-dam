<?php

declare(strict_types=1);

namespace App\Security\Authenticator;

use App\Entity\User;
use App\Model\UgcCookieConfiguration;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Exception;
use Lcobucci\JWT\Token\Plain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use function Symfony\Component\String\u;

final class UgcAuthenticator extends AbstractUgcAuthenticator
{
    public function __construct(
        private readonly Configuration $jwtUgcConfiguration,
        private readonly UgcCookieConfiguration $ugcSecurityConfiguration,
    ) {
    }

    public function supports(Request $request): bool
    {
        $hasUgcCookies = $request->cookies->has($this->ugcSecurityConfiguration->getJwtPayloadPartName())
            && $request->cookies->has($this->ugcSecurityConfiguration->getJwtSignaturePartName());

        return ($hasUgcCookies || $request->headers->has('Authorization'))
                && false === $request->cookies->has($this->ugcSecurityConfiguration->getJwtImpName());
    }

    public function authenticate(Request $request): Passport
    {
        $credentialsToken = $this->getCredentials($request);
        $credentialsChecker = function (Plain $token, User $user): bool {
            return $this->checkCredentials($token, $user);
        };

        return new Passport(
            new UserBadge((string) $credentialsToken->claims()->get('sub')),
            new CustomCredentials($credentialsChecker, $credentialsToken)
        );
    }

    private function getCredentials(Request $request): Plain
    {
        $plainToken = $this->getPlainAccessTokenFromRequest($request);

        try {
            return $this->jwtUgcConfiguration->parser()->parse($plainToken);
        } catch (Exception $exception) {
            throw new AccessDeniedException($plainToken, $exception);
        }
    }

    private function checkCredentials(Plain $credentials, User $user): bool
    {
        $this->checkToken($this->jwtUgcConfiguration, $credentials, $user);

        if (false === $user->isEnabled()) {
            throw new AuthenticationException(sprintf('User (%d) is not active or is disabled!', (int) $user->getId()));
        }
        $email = $credentials->claims()->get('eml');
        if ($email && $user->getEmail() !== $email) { // update user email address on a next crud action if changed
            $user->setEmail($email);
        }

        return true;
    }

    private function getPlainAccessTokenFromRequest(Request $request): string
    {
        if ($request->headers->has('Authorization')) {
            return u((string) $request->headers->get('Authorization'))
                ->replaceMatches('~Bearer[\s+]~', '')
                ->trim()
                ->toString();
        }

        return sprintf(
            '%s.%s',
            (string) $request->cookies->get($this->ugcSecurityConfiguration->getJwtPayloadPartName()),
            (string) $request->cookies->get($this->ugcSecurityConfiguration->getJwtSignaturePartName()),
        );
    }
}
