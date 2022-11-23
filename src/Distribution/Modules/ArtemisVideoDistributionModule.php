<?php

declare(strict_types=1);


namespace App\Distribution\Modules;

use AnzuSystems\CoreDamBundle\Distribution\DistributionModuleInterface;
use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use App\Entity\ArtemisDistribution;

final class ArtemisVideoDistributionModule implements DistributionModuleInterface
{
    public function distribute(Distribution $distribution): void
    {
        // TODO: Implement distribute() method.
    }

    public function redistribute(Distribution $distribution): void
    {
        // TODO: Implement redistribute() method.
    }

    public function supportsAssetType(): array
    {
        return [
            AssetType::Video
        ];
    }

    public static function getDefaultKeyName(): string
    {
        return self::class;
    }

    public function waitForRemoteProcessing(): bool
    {
        return false;
    }

    public function isAuthenticated(string $distributionService): bool
    {
        return true;
    }

    public static function supportsDistributionResourceName(): string
    {
        return ArtemisDistribution::getResourceName();
    }
}
