<?php

declare(strict_types=1);

namespace App\Util;

use App\Model\Enum\GateStatus;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class GateStatusResolver
{
    public const string ACCESS_CONTENT_HEADER = 'X-GW-Access-Content';
    public const string ACCESS_NO_ADVERT = 'X-GW-Access-No-Advert';

    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function getLockStatus(): GateStatus
    {
        return $this->resolve(headerName: self::ACCESS_CONTENT_HEADER);
    }

    public function getNoAdvertStatus(): GateStatus
    {
        return $this->resolve(headerName: self::ACCESS_NO_ADVERT);
    }

    private function resolve(string $headerName): GateStatus
    {
        $accessContent = $this->getRequest()->headers->get($headerName);
        if (empty($accessContent)) {
            $accessContent = GateStatus::LockedUserNotLoggedOrNotPaid->value;
        }

        return GateStatus::tryFrom($accessContent) ?? GateStatus::Default;
    }

    private function getRequest(): Request
    {
        $request = $this->requestStack->getMainRequest();
        if (null === $request) {
            throw new RuntimeException('There is no main request. Gate status cannot be resolved.');
        }

        return $request;
    }
}
