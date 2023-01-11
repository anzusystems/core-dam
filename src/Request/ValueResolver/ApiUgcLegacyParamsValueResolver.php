<?php

declare(strict_types=1);

namespace App\Request\ValueResolver;

use App\ApiFilter\ApiUgcLegacyParams;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ApiUgcLegacyParamsValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (ApiUgcLegacyParams::class === $argument->getType()) {
            return [(new ApiUgcLegacyParams())->setFromRequest($request)];
        }

        return [];
    }
}
