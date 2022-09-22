<?php

declare(strict_types=1);

namespace AnzuSystems\AuthBundle\Util;

use Anzu\AuthenticationBundle\Exception\InvalidJwtException;
use Anzu\AuthenticationBundle\Exception\NotFoundAccessTokenException;
use Anzu\AuthenticationBundle\Util\Helper\ConditionHelper;
use AnzuSystems\AuthBundle\Configuration\CookieConfiguration;
use AnzuSystems\AuthBundle\Model\RefreshTokenDto;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Parser;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function Symfony\Component\String\u;

final class HttpUtil
{
    public function __construct(
        private readonly CookieConfiguration $cookieConfiguration,
    ) {
    }

    public function grabJwtFromRequest(Request $request): Token
    {
        $jwt = $this->getPlainAccessTokenFromRequest($request);

        if (empty($jwt)) {
            throw NotFoundAccessTokenException::create();
        }

        return (new Parser(new JoseEncoder()))->parse($jwt);
    }

    public function grabRefreshTokenFromRequest(Request $request): array
    {
        $rawRefreshToken = (string) $request->cookies->get($this->cookieConfiguration->getRefreshTokenCookieName());
        $token = [$tokenId, $tokenHash] = explode('.', $rawRefreshToken, 2);
        if (ConditionHelper::isOneOfVariablesEmpty($tokenId, $tokenHash)) {
            throw InvalidJwtException::create('');
        }

        return $token;
    }

    public function storeJwtOnResponse(Response $response, Token $token): void
    {
        $rawToken = $token->toString();
        [$header, $claims, $signature] = explode('.', $rawToken, 3);

        if (ConditionHelper::isOneOfVariablesEmpty($header, $claims, $signature)) {
            throw InvalidJwtException::create($rawToken);
        }

        $payloadCookie = $this->createCookie(
            $this->cookieConfiguration->getJwtPayloadCookieName(),
            $header . '.' . $claims,
            $this->cookieConfiguration->getJwtLifetime(),
            false
        );
        $signatureCookie = $this->createCookie(
            $this->cookieConfiguration->getJwtSignatureCookieName(),
            $signature,
            $this->cookieConfiguration->getRefreshTokenLifetime()
        );

        $response->headers->setCookie($payloadCookie);
        $response->headers->setCookie($signatureCookie);
    }

    public function storeRefreshTokenOnResponse(Response $response, RefreshTokenDto $refreshTokenDto): void
    {
        $refreshTokenCookie = $this->createCookie(
            $this->cookieConfiguration->getRefreshTokenCookieName(),
            $refreshTokenDto->getTokenId() . ':' . $refreshTokenDto->getTokenHash(),
            $this->cookieConfiguration->getRefreshTokenLifetime(),
        );

        $response->headers->setCookie($refreshTokenCookie);
    }

    private function createCookie(
        string $name,
        string $value,
        int $ttl,
        bool $httpOnly = true,
    ): Cookie {
        return Cookie::create(
            $name,
            $value,
            time() + $ttl,
            '/',
            $this->cookieConfiguration->getDomain(),
            $this->cookieConfiguration->isSecure(),
            $httpOnly,
            false,
            Cookie::SAMESITE_STRICT
        );
    }

    private function getPlainAccessTokenFromRequest(Request $request): ?string
    {
        if ($request->headers->has('authorization')) {
            return u((string) $request->headers->get('authorization'))
                ->replaceMatches('~Bearer[\s+]~', '')
                ->trim()
                ->toString();
        }

        if ($request->cookies->has($this->cookieConfiguration->getJwtPayloadCookieName())
            && $request->cookies->has($this->cookieConfiguration->getJwtSignatureCookieName())
        ) {
            return u((string) $request->cookies->get($this->cookieConfiguration->getJwtPayloadCookieName()))
                ->append('.')
                ->append((string) $request->cookies->get($this->cookieConfiguration->getJwtSignatureCookieName()))
                ->trim()
                ->toString();
        }

        return '';
    }
}
