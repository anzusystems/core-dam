<?php

declare(strict_types=1);

namespace App\Model;

use AnzuSystems\CoreDamBundle\Entity\AssetLicence;

/**
 * @deprecated - Should be removed when we get rid of sensio/framework-extra-bundle.
 *               For now, we can't inject AssetLicence directly, because DoctrineParamConverter would end up with an error.
 */
final readonly class AssetLicenceDecorator
{
    public function __construct(
        private AssetLicence $assetLicence,
    ) {
    }

    public function getAssetLicence(): AssetLicence
    {
        return $this->assetLicence;
    }
}
