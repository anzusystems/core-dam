<?php

declare(strict_types=1);

namespace App\Domain\Asset;

use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\PublicExport;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetStatus;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use App\Exception\PubNotFoundHttpException;
use App\Model\Domain\Asset\AbstractAssetPubDecorator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class AssetPubFacade
{
    public function __construct(
        private VideoAssetPubBuilder $videoAssetPubBuilder,
        private AudioAssetPubBuilder $audioAssetPubBuilder,
    ) {
    }

    /**
     * @throws NotFoundHttpException
     */
    public function decorateAsset(Asset $asset, PublicExport $publicExport): AbstractAssetPubDecorator
    {
        $decorator = $this->getDecorator($asset, $publicExport);
        if (null === $decorator) {
            throw new PubNotFoundHttpException('Asset not found');
        }

        if ($asset->getSiblingToAsset() instanceof Asset) {
            $decorator->setSibling($this->getDecorator($asset->getSiblingToAsset(), $publicExport));
        }

        return $decorator;
    }

    private function getDecorator(Asset $asset, PublicExport $publicExport): ?AbstractAssetPubDecorator
    {
        if ($asset->getAttributes()->getStatus()->isNot(AssetStatus::WithFile)) {
            return null;
        }

        if ($asset->getAssetType()->is(AssetType::Audio)) {
            return $this->audioAssetPubBuilder->getAudioDecorator($asset, $publicExport);
        }
        if ($asset->getAssetType()->is(AssetType::Video)) {
            return $this->videoAssetPubBuilder->getVideoDecorator($asset);
        }

        return null;
    }
}
