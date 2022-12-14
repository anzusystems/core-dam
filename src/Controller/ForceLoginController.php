<?php

declare(strict_types=1);

namespace App\Controller;

use AnzuSystems\AuthBundle\Exception\InvalidJwtException;
use AnzuSystems\AuthBundle\Exception\MissingConfigurationException;
use AnzuSystems\AuthBundle\Util\HttpUtil;
use AnzuSystems\AuthBundle\Util\JwtUtil;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use App\Entity\User;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ForceLoginController extends AbstractApiController
{
    public function __construct(
        private readonly JwtUtil $jwtUtil,
        private readonly HttpUtil $httpUtil,
    ) {
    }

    /**
     * @throws MissingConfigurationException
     * @throws InvalidJwtException
     */
    #[Route('/force/login/{user}', condition: "env('APP_DEPLOY_ENV') !== 'production'")]
    public function login(Request $request, User $user): Response
    {
        $expiresAt = new DateTimeImmutable('+1 week');
        $jwtToken = $this->jwtUtil->create($user->getAuthId(), $expiresAt);

        $response = new RedirectResponse($this->httpUtil->getAuthRedirectUrlFromRequest($request));
        if ($this->isJsonRequest($request)) {
            $response = new JsonResponse([
                'accessToken' => $jwtToken->toString(),
            ]);
        }
        $this->httpUtil->storeJwtOnResponse($response, $jwtToken, $expiresAt);

        return $response;
    }
}
