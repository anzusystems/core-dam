<?php

declare(strict_types=1);

namespace App\Model\Domain\Asset;

use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;

final class AssetPodcastEpisodePubDecorator
{
    private Asset $asset;
    private array $media = [];

    public static function getInstance(
        Asset $asset,
        array $media,
    ): self {
        return (new self())
            ->setAsset($asset)
            ->setMedia($media)
        ;
    }

    public function getAsset(): Asset
    {
        return $this->asset;
    }

    public function setAsset(Asset $asset): self
    {
        $this->asset = $asset;

        return $this;
    }

    #[Serialize(handler: EntityIdHandler::class)]
    public function getId(): Asset
    {
        return $this->asset;
    }

    #[Serialize]
    public function getMedia(): array
    {
        return $this->media;
    }

    public function setMedia(array $media): self
    {
        $this->media = $media;
        return $this;
    }
}
