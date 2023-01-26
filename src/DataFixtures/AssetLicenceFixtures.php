<?php

declare(strict_types=1);

namespace App\DataFixtures;

use AnzuSystems\CommonBundle\DataFixtures\Fixtures\AbstractFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\AssetLicenceFixtures as BaseAssetLicenceFixtures;
use AnzuSystems\CoreDamBundle\Domain\AssetLicence\AssetLicenceManager;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use Symfony\Component\Console\Helper\ProgressBar;

final class AssetLicenceFixtures extends AbstractFixtures
{
    public const BLOG_DEFAULT_ASSET_LICENCE_ID = BaseAssetLicenceFixtures::DEFAULT_LICENCE_ID + 1;

    public function __construct(
        private readonly AssetLicenceManager $assetLicenceManager,
    ) {
    }

    public static function getIndexKey(): string
    {
        return AssetLicence::class;
    }

    public function useCustomId(): bool
    {
        return true;
    }

    public function load(ProgressBar $progressBar): void
    {
        foreach ($progressBar->iterate($this->getData()) as $licence) {
            $licence = $this->assetLicenceManager->create($licence, false);
            $this->addToRegistry($licence, $licence->getId());
        }
        $this->assetLicenceManager->flush();
    }

    /**
     * @return iterable<AssetLicence>
     */
    private function getData(): iterable
    {
        $blogExtSystem = $this->entityManager->find(ExtSystem::class, 4);

        $user = new AssetLicence();
        $user
            ->setId(self::BLOG_DEFAULT_ASSET_LICENCE_ID)
            ->setExtId('1')
            ->setExtSystem($blogExtSystem)
        ;

        yield $user;
    }
}
