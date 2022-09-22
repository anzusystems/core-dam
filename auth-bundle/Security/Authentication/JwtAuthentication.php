<?php

declare(strict_types=1);

namespace AnzuSystems\AuthBundle\Security\Authentication;

use AnzuSystems\AuthBundle\Contracts\SsoUserInterface;
use AnzuSystems\AuthBundle\Util\HttpUtil;
use AnzuSystems\AuthBundle\Util\JwtUtil;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\RegisteredClaims;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

final class JwtAuthentication extends AbstractAuthenticator
{
    public function __construct(
        private readonly HttpUtil $httpUtil,
        private readonly JwtUtil $jwtUtil,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return true;
    }

    public function authenticate(Request $request): Passport
    {
        $jwtToken = $this->httpUtil->grabJwtFromRequest($request);

        return new Passport(
            new UserBadge($jwtToken->claims()->get(RegisteredClaims::SUBJECT)),
            new CustomCredentials($this->checkCredentials(...), $jwtToken),
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['message' => 'The resource owner or authorization server denied the request.'], 401);
    }

    private function checkCredentials(Token $token, SsoUserInterface $user): bool
    {
        if (false === $user->isEnabled()) {
            throw new AuthenticationException('User disabled!');
        }

        return $this->jwtUtil->validate($token);
    }
}
