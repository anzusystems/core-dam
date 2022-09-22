<?php

declare(strict_types=1);

namespace AnzuSystems\AuthBundle\Model;

use Symfony\Component\HttpFoundation\Request;

final class DeviceDto
{
    private const DEVICE_INFO_HEADERS = [
        'User-Agent',
        'Sec-CH-UA',
        'Sec-CH-UA-Arch',
        'Sec-CH-UA-Bitness',
        'Sec-CH-UA-Mobile',
        'Sec-CH-UA-Model',
        'Sec-CH-UA-Platform',
        'Sec-CH-UA-Full-Version-List',
    ];

    public function __construct(
        private readonly string $ip,
        private readonly array $info
    ) {
    }

    public static function createFromRequest(Request $request): self
    {
        $headers = [];
        foreach (self::DEVICE_INFO_HEADERS as $header) {
            if ($request->headers->has($header)) {
                $headers[$header] = $request->headers->get($header);
            }
        }

        return new self(
            ip: $request->getClientIp(),
            info: array_filter($headers),
        );
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getInfo(): array
    {
        return $this->info;
    }
}
