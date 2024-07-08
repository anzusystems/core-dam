<?php

declare(strict_types=1);

namespace App\DataFixtures;

use AnzuSystems\CommonBundle\DataFixtures\Fixtures\AbstractFixtures;
use AnzuSystems\CoreDamBundle\DataFixtures\AssetLicenceFixtures as BaseAssetLicenceFixtures;
use AnzuSystems\CoreDamBundle\Domain\AssetLicence\AssetLicenceManager;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * @extends AbstractFixtures<AssetLicence>
 */
final class AssetLicenceFixtures extends AbstractFixtures
{
    public const int BLOG_ONE_LICENCE_ID = BaseAssetLicenceFixtures::DEFAULT_LICENCE_ID + 10_000;
    public const int BLOG_ONE_EXT_ID = 1;
    public const int BLOG_TWO_LICENCE_ID = self::BLOG_ONE_LICENCE_ID + 1;
    public const int BLOG_TWO_EXT_ID = 2;
    public const int BLOG_THREE_LICENCE_ID = self::BLOG_TWO_LICENCE_ID + 1;
    public const int BLOG_THREE_EXT_ID = 3;
    public const int BLOG_FOUR_LICENCE_ID = self::BLOG_THREE_LICENCE_ID + 1;
    public const int BLOG_FOUR_EXT_ID = 4;
    public const int BLOG_FIVE_LICENCE_ID = self::BLOG_FOUR_LICENCE_ID + 1;
    public const int BLOG_FIVE_EXT_ID = 5;
    public const int BLOG_SIX_LICENCE_ID = self::BLOG_FIVE_LICENCE_ID + 1;
    public const int BLOG_SIX_EXT_ID = 6;

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
        /** @var ExtSystem $blogExtSystem */
        $blogExtSystem = $this->entityManager->find(ExtSystem::class, 4);

        $licenceOne = new AssetLicence();
        $licenceOne
            ->setId(self::BLOG_ONE_LICENCE_ID)
            ->setExtId((string) self::BLOG_ONE_EXT_ID)
            ->setExtSystem($blogExtSystem)
            ->setName('Anzulák: Blog plný radosti')
        ;

        yield $licenceOne;

        $licenceTwo = new AssetLicence();
        $licenceTwo
            ->setId(self::BLOG_TWO_LICENCE_ID)
            ->setExtId((string) self::BLOG_TWO_EXT_ID)
            ->setExtSystem($blogExtSystem)
            ->setName('Kavickar: Kavo-Blog')
        ;

        yield $licenceTwo;

        $licenceThree = new AssetLicence();
        $licenceThree
            ->setId(self::BLOG_THREE_LICENCE_ID)
            ->setExtId((string) self::BLOG_THREE_EXT_ID)
            ->setExtSystem($blogExtSystem)
            ->setName('Slonik: PHPckar')
        ;

        yield $licenceThree;

        $licenceFour = new AssetLicence();
        $licenceFour
            ->setId(self::BLOG_FOUR_LICENCE_ID)
            ->setExtId((string) self::BLOG_FOUR_EXT_ID)
            ->setExtSystem($blogExtSystem)
            ->setName('Vata')
        ;

        yield $licenceFour;

        $licenceFive = new AssetLicence();
        $licenceFive
            ->setId(self::BLOG_FIVE_LICENCE_ID)
            ->setExtId((string) self::BLOG_FIVE_EXT_ID)
            ->setExtSystem($blogExtSystem)
            ->setName('Pixel')
        ;

        yield $licenceFive;

        $licenceSix = new AssetLicence();
        $licenceSix
            ->setId(self::BLOG_SIX_LICENCE_ID)
            ->setExtId((string) self::BLOG_SIX_EXT_ID)
            ->setExtSystem($blogExtSystem)
            ->setName('Krčmárove patálie')
        ;

        yield $licenceSix;
    }
}
