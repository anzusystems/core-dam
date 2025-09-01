<?php

declare(strict_types=1);

namespace App\Tests\HttpClient;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

final class CmsClientMock extends AbstractMock
{
    public function __invoke(): MockHttpClient
    {
        return new MockHttpClient(
            fn (string $method, string $url, array $options = []) => $this->getResponse($method, $url, $options)
        );
    }

    private function getResponse(string $method, string $url, array $options = []): MockResponse
    {
        if (str_starts_with($url, '/api/sys/v1/dam/media')) {
            return new MockResponse(
                [],
                [
                    'http_code' => Response::HTTP_OK,
                ]
            );
        }

        return new MockResponse(
            [],
            [
                'http_code' => Response::HTTP_OK,
            ]
        );
    }
}
