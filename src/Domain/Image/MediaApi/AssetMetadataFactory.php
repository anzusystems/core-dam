<?php

declare(strict_types=1);

namespace App\Domain\Image\MediaApi;

use App\Model\Domain\Asset\AssetFileMediaApiDecorator;
use App\Model\Domain\AssetMetadata\MediaApiMetadata;

final class AssetMetadataFactory
{
    public function updateFromMediaApi(MediaApiMetadata $oldMetadata, AssetFileMediaApiDecorator $dto): MediaApiMetadata
    {
        $oldMetadata->setDescription($dto->getDescription());
        $oldMetadata->setFocusX($dto->getFocusX());
        $oldMetadata->setFocusY($dto->getFocusY());
        $oldMetadata->addMediaApiId($dto->getMediaApiId());
        $oldMetadata->addMediaApiPath($dto->getFullPath());

        return $oldMetadata;
    }

    public function updateFromMediaApiMetadata(MediaApiMetadata $oldMetadata, MediaApiMetadata $newMetadata): MediaApiMetadata
    {
        if (false === empty($newMetadata->getDescription())) {
            $oldMetadata->setDescription($newMetadata->getDescription());
        }
        $oldMetadata->setFocusX($newMetadata->getFocusX());
        $oldMetadata->setFocusY($newMetadata->getFocusY());

        foreach ($newMetadata->getMediaApiPaths() as $path) {
            $oldMetadata->addMediaApiPath($path);
        }

        foreach ($newMetadata->getMediaApiIds() as $id) {
            $oldMetadata->addMediaApiId($id);
        }

        return $oldMetadata;
    }
}
