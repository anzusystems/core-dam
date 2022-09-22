<?php

declare(strict_types=1);

namespace AnzuSystems\AuthBundle\Security;

use AnzuSystems\AuthBundle\Configuration\CookieConfiguration;
use AnzuSystems\AuthBundle\Model\DeviceDto;
use AnzuSystems\AuthBundle\Model\RefreshTokenDto;
use AnzuSystems\AuthBundle\Util\HttpUtil;
use AnzuSystems\AuthBundle\Util\JwtUtil;
use DateTimeImmutable;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private readonly JwtUtil $jwtUtil,
        private readonly HttpUtil $httpUtil,
        private readonly CookieConfiguration $cookieConfiguration,
    ) {
    }

    /**
     * @throws Exception
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        $user = $token->getUser();
        $jwt = $this->jwtUtil->create($user->getUserIdentifier());
        $refreshToken = new RefreshTokenDto(
            tokenId: uuid_create(),
            tokenHash: bin2hex(random_bytes(32)),
            device: DeviceDto::createFromRequest($request),
            expiresAt: DateTimeImmutable::createFromFormat('U', (string) $this->cookieConfiguration->getRefreshTokenLifetime())
        );

        $response = new JsonResponse([
            'access_token' => $jwt->toString(),
            'refresh_token' => $refreshToken->getTokenId() . ':' . $refreshToken->getTokenHash(),
        ]);
        $this->httpUtil->storeJwtOnResponse($response, $jwt);
        $this->httpUtil->storeRefreshTokenOnResponse($response, $refreshToken);

        return $response;
    }
}
