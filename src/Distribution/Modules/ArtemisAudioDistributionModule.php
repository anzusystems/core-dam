<?php

declare(strict_types=1);


namespace App\Distribution\Modules;

use AnzuSystems\CoreDamBundle\Distribution\AbstractDistributionModule;
use AnzuSystems\CoreDamBundle\Distribution\DistributionModuleInterface;
use AnzuSystems\CoreDamBundle\Entity\CustomDistribution;
use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;

final class ArtemisAudioDistributionModule extends AbstractDistributionModule implements DistributionModuleInterface
{
    private const ARTICLE_WEB_URL = 'articleWebUrl';
    private const ARTICLE_ADMIN_URL = 'articleAdminUrl';
    private const MEDIA_ADMIN_URL = 'mediaAdminUrl';

    /**
     * @param CustomDistribution $distribution
     */
    public function distribute(Distribution $distribution): void
    {
        // todo implement
        $distribution->setExtId('123');
        $customDistributionData = [self::MEDIA_ADMIN_URL => 'https://url.sme.sk',];

        if ($distribution->getCustomData()['createArticle'] ?? false) {
            $customDistributionData[self::ARTICLE_WEB_URL] =  'https://url.sme.sk';
            $customDistributionData[self::ARTICLE_ADMIN_URL] =  'https://url.sme.sk';
        }

        $distribution->setDistributionData($customDistributionData);
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

    public function isAuthenticated(string $distributionService): bool
    {
        return true;
    }
}