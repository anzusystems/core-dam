<?php

declare(strict_types=1);

namespace App\Domain\AssetMetadata;

use AnzuSystems\CommonBundle\Domain\AbstractManager;
use AnzuSystems\CommonBundle\Traits\SerializerAwareTrait;
use AnzuSystems\CoreDamBundle\Entity\AssetMetadata;
use AnzuSystems\SerializerBundle\Exception\SerializerException;

final class AssetMetadataManager extends AbstractManager
{
    use SerializerAwareTrait;

    public function updateMetadataFromObject(AssetMetadata $assetMetadata, object $object, bool $flush = true): AssetMetadata
    {
        $data = $this->serializer->toArray($object);
        $customData = $assetMetadata->getCustomData();
        foreach ($data as $key => $value) {
            $customData[$key] = $value;
        }

        $assetMetadata->setCustomData($customData);

        return $assetMetadata;
    }

    /**
     * @psalm-suppress InvalidReturnStatement
     * @psalm-suppress InvalidReturnType
     *
     * @template T of object
     *
     * @param class-string<T> $className
     *
     * @return T
     *
     * @throws SerializerException
     */
    public function getObjectFromMetadata(AssetMetadata $assetMetadata, string $className): object
    {
        return $this->serializer->fromArray($assetMetadata->getCustomData(), $className);
    }
}
