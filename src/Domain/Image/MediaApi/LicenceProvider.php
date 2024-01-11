<?php

declare(strict_types=1);

namespace App\Domain\Image\MediaApi;

use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use AnzuSystems\CoreDamBundle\Repository\AssetLicenceRepository;
use App\Model\Domain\Asset\AssetFileMediaApiCreateDecorator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class LicenceProvider
{
    private const int STOCK_SPECTATOR = 2;
    private const int STOCK_SME = 5;
    private const int STOCK_XBLOCK = 8;
    private const int STOCK_QUIZ = 10;
    private const int STOCK_MAGAZIN = 17;
    //    private const int PRESS_NEWS = 19; // skip
    private const int STOCK_KOMERCNE = 25;
    //    private const int VIDEO = 32; // skip
    //    private const int WASTE = 34; // skip
    //    private const int WASTE_MACRO = 37; // skip
    private const int STOCK_AUTHORS = 38;
    //    private const int GEO_LOCATION = 40; // skip
    private const int STOCK_ACCELERATED_WIGET = 43;
    private const int STOCK_ARTICLE_SHORT = 45;
    private const int STOCK_ARTICLE_BACKGROUND = 46;

    private const int LICENCE_CMS = 100_000;
    private const int LICENCE_SPECTATOR = 100_001;
    private const int LICENCE_X_BLOCK = 100_002;
    private const int LICENCE_MAGAZIN = 100_003;
    private const int LICENCE_KOMERCNE = 100_004;
    private const int LICENCE_AUTHOR = 100_006;
    private const int LICENCE_SOCIAL = 100_007;
    private const int LICENCE_SCRAPER = 101_000;

    private const array LICENCE_MAP = [
        self::STOCK_SPECTATOR => self::LICENCE_SPECTATOR,
        self::STOCK_SME => self::LICENCE_CMS,
        self::STOCK_XBLOCK => self::LICENCE_X_BLOCK,
        self::STOCK_QUIZ => self::LICENCE_CMS,
        self::STOCK_MAGAZIN => self::LICENCE_MAGAZIN,
        self::STOCK_KOMERCNE => self::LICENCE_KOMERCNE,
        self::STOCK_AUTHORS => self::LICENCE_AUTHOR,
        self::STOCK_ACCELERATED_WIGET => self::LICENCE_SCRAPER,
        self::STOCK_ARTICLE_SHORT => self::LICENCE_SOCIAL,
        self::STOCK_ARTICLE_BACKGROUND => self::LICENCE_SOCIAL,
    ];

    public function __construct(
        private AssetLicenceRepository $assetLicenceRepository,
    ) {
    }

    public function getLicence(AssetFileMediaApiCreateDecorator $dto): AssetLicence
    {
        $licenceId = self::LICENCE_MAP[$dto->getIdStock()]
            ?? throw new NotFoundHttpException('Unknown stock');

        $licence = $this->assetLicenceRepository->find($licenceId);
        if (null === $licence) {
            throw new NotFoundHttpException('Licence not found');
        }
        return $licence;
    }

    public function getExtSystem(AssetFileMediaApiCreateDecorator $dto): ExtSystem
    {
        return $this->getLicence($dto)->getExtSystem();
    }
}
