<?php

declare(strict_types=1);

namespace App\Request\ValueResolver;

use App\Model\Request\ApiPubParams;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ApiPubParamValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (ApiPubParams::class === $argument->getType()) {
            return [ApiPubParams::createFromRequest($request)];
        }

        return [];
    }
}
