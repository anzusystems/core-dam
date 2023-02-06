<?php

declare(strict_types=1);

namespace App\Tests\HttpClient;

use App\App;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

final class ArtemisClientMock extends AbstractMock
{
    private const AUDIO_TYPE = 'audio';
    private const VIDEO_TYPE = 'video';

    public function __invoke(): MockHttpClient
    {
        return new MockHttpClient(
            fn (string $method, string $url, array $options = []) => $this->getResponse($method, $url, $options)
        );
    }

    private function getResponse(string $method, string $url, array $options = []): MockResponse
    {
        $payload = json_decode($options['body'] ?? '{}', true);

        if (self::AUDIO_TYPE === $payload['type']) {
            return new MockResponse(
                $this->getTestDataFile('artemisAudioPodcast.json'),
                [
                    'http_code' => Response::HTTP_OK,
                ]
            );
        }

        if (self::VIDEO_TYPE === $payload['type']) {
            return new MockResponse(
                $this->getTestDataFile('artemisVideoPodcast.json'),
                [
                    'http_code' => Response::HTTP_OK,
                ]
            );
        }

        return new MockResponse('{}', ['http_code' => Response::HTTP_OK]);
    }
}
