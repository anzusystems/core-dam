<?php

declare(strict_types=1);

namespace App\Domain\Image\MediaApi;

use AnzuSystems\CoreDamBundle\Domain\Keyword\KeywordManager;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use AnzuSystems\CoreDamBundle\Entity\Keyword;
use AnzuSystems\CoreDamBundle\Helper\StringHelper;
use AnzuSystems\CoreDamBundle\Repository\AuthorRepository;
use AnzuSystems\CoreDamBundle\Repository\KeywordRepository;
use App\Model\Domain\Asset\AssetFileMediaApiCreateDecorator;
use App\Model\Domain\Asset\AssetFileMediaApiDecorator;
use App\Model\Domain\Asset\AssetFileMediaApiResponseDecorator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final readonly class KeywordProvider
{
    public function __construct(
        private KeywordRepository $repository,
        private KeywordManager $keywordManager,
        private LicenceProvider $licenceProvider,
    ) {
    }

    /**
     * @return Collection<string, Keyword>
     */
    public function getKeywordsFromCreateDto(AssetFileMediaApiCreateDecorator $dto): Collection
    {
        return $this->getKeywordsFromUpdateDto(
            dto: $dto,
            extSystem: $this->licenceProvider->getExtSystem($dto)
        );
    }

    /**
     * @return Collection<string, Keyword>
     */
    public function getKeywordsFromUpdateDto(AssetFileMediaApiDecorator $dto, ExtSystem $extSystem): Collection
    {
        $keywordTitles = explode(',', $dto->getKeywords());

        /** @var Collection<string, Keyword> $keywordsCol */
        $keywordsCol = new ArrayCollection();
        array_map(
            function (string $keywordTitle) use ($keywordsCol, $extSystem): void {
                $keyword = $this->getKeyword(
                    title: $keywordTitle,
                    extSystem: $extSystem
                );

                if ($keyword) {
                    $keywordsCol->set((string) $keyword->getId(), $keyword);
                }
            },
            $keywordTitles
        );

        return $keywordsCol;
    }

    private function getKeyword(string $title, ExtSystem $extSystem): ?Keyword
    {
        $title = StringHelper::parseString(input: $title, length: 255);

        if (empty($title)) {
            return null;
        }

        $keyword = $this->repository->findOneByNameAndExtSystem(
            name: $title,
            extSystem: $extSystem
        );

        if ($keyword) {
            return $keyword;
        }

        return $this->keywordManager->create(
            keyword: (new Keyword())
                ->setExtSystem($extSystem)
                ->setName($title),
        );
    }
}
