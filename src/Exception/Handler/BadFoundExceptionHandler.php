<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use AnzuSystems\CommonBundle\Exception\Handler\ExceptionHandlerInterface;
use AnzuSystems\Contracts\AnzuApp;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

final class BadFoundExceptionHandler implements ExceptionHandlerInterface
{
    public const string ERROR = 'bad_request';

    public function getErrorResponse(Throwable $exception): JsonResponse
    {
        return new JsonResponse(
            [
                'error' => self::ERROR,
                'detail' => $exception->getMessage(),
                'contextId' => AnzuApp::getContextId(),
            ],
            JsonResponse::HTTP_BAD_REQUEST
        );
    }

    public function getSupportedExceptionClasses(): array
    {
        return [BadRequestHttpException::class];
    }
}
