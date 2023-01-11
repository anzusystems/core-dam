<?php

declare(strict_types=1);

namespace App\Security\Authenticator;

use App\Entity\User;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

abstract class AbstractUgcAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function start(Request $request, AuthenticationException $authException = null): JsonResponse
    {
        return $this->createUnauthorizedResponse();
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return $this->createUnauthorizedResponse();
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    protected function checkToken(Configuration $jwtConfiguration, Plain $token, User $user): void
    {
        if (false === $token->claims()->get('cnf')) {
            throw new AuthenticationException(sprintf('User (%d) has not confirmed account!', (int) $user->getId()));
        }

        try {
            $jwtConfiguration->validator()->assert($token, ...$jwtConfiguration->validationConstraints());
        } catch (RequiredConstraintsViolated $exception) {
            $exception = new AccessDeniedException($token->toString(), $exception);
            $exception->setSubject($user);

            throw $exception;
        }
    }

    private function createUnauthorizedResponse(): JsonResponse
    {
        return new JsonResponse(
            ['message' => 'The resource owner or authorization server denied the request.'],
            Response::HTTP_UNAUTHORIZED
        );
    }
}
