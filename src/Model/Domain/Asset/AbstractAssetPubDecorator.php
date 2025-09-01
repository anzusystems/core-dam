<?php

declare(strict_types=1);

namespace App\Model\Domain\Asset;

use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;

abstract class AbstractAssetPubDecorator
{
    protected Asset $asset;
    protected string $metadataTitleField;
    protected ?AbstractAssetPubDecorator $sibling = null;

    public static function getBaseInstance(Asset $asset, string $metadataTitleField): static
    {
        $instance = new static();
        $instance->setAsset($asset);
        $instance->setMetadataTitleField($metadataTitleField);
        return $instance;
    }

    public function getMetadataTitleField(): string
    {
        return $this->metadataTitleField;
    }

    public function setMetadataTitleField(string $metadataTitleField): self
    {
        $this->metadataTitleField = $metadataTitleField;
        return $this;
    }

    #[Serialize]
    public function getSibling(): ?self
    {
        return $this->sibling;
    }

    public function setSibling(?self $sibling): static
    {
        $this->sibling = $sibling;
        return $this;
    }

    public function setAsset(Asset $asset): static
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
    public function getAssetType(): AssetType
    {
        return $this->asset->getAssetType();
    }

    #[Serialize]
    public function getTitle(): string
    {
        return $this->asset->getMetadata()->getCustomData()[$this->getMetadataTitleField()] ?? '';
    }
}
