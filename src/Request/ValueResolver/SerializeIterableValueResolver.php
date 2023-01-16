<?php

declare(strict_types=1);

namespace App\Request\ValueResolver;

use AnzuSystems\SerializerBundle\Exception\SerializerException;
use AnzuSystems\SerializerBundle\Serializer;
use App\Model\Attribute\SerializeIterableParam;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Routing\Exception\InvalidArgumentException;

final readonly class SerializeIterableValueResolver implements ValueResolverInterface
{
    public function __construct(
        private Serializer $serializer,
    ) {
    }

    /**
     * @throws SerializerException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $attribute = $argument->getAttributesOfType(SerializeIterableParam::class)[0] ?? null;
        if (false === ($attribute instanceof SerializeIterableParam)) {
            return [];
        }

        /** @var ArrayCollection $items */
        $items = $this->serializer->deserializeIterable($request->getContent(), $attribute->type, new ArrayCollection());
        if ($attribute->maxItems && $items->count() > $attribute->maxItems) {
            throw new InvalidArgumentException('max_items_reached');
        }


        return [
            $items,
        ];
    }
}
