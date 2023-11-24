<?php

declare(strict_types=1);

namespace App\Domain\Image\MediaApi;

use AnzuSystems\CoreDamBundle\Domain\Author\AuthorManager;
use AnzuSystems\CoreDamBundle\Entity\Author;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use AnzuSystems\CoreDamBundle\Helper\StringHelper;
use AnzuSystems\CoreDamBundle\Repository\AuthorRepository;
use App\Model\Domain\Asset\AssetFileMediaApiCreateDecorator;
use App\Model\Domain\Asset\AssetFileMediaApiDecorator;
use App\Model\Domain\Asset\AssetFileMediaApiResponseDecorator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final readonly class AuthorProvider
{
    public function __construct(
        private AuthorRepository $repository,
        private AuthorManager $authorManager,
        private LicenceProvider $licenceProvider,
    ) {
    }

    /**
     * @param string[] $authors
     *
     * @return Collection<string, Author>
     */
    public function getAuthorCollFromArray(array $authors, ExtSystem $extSystem): Collection
    {
        /** @var Collection<string, Author> $authorColl */
        $authorColl = new ArrayCollection();

        foreach ($authors as $title) {
            $author = $this->getAuthor($title, $extSystem);
            if ($author) {
                $authorColl->set((string) $author->getId(), $author);
            }
        }

        return $authorColl;
    }

    /**
     * @return Collection<string, Author>
     */
    public function getAuthorCollFromDto(AssetFileMediaApiDecorator $dto, ExtSystem $extSystem): Collection
    {
        /** @var Collection<string, Author> $authorColl */
        $authorColl = new ArrayCollection();

        $author = $this->getAuthor(
            title: $dto->getAuthor(),
            extSystem: $extSystem
        );

        if ($author) {
            $authorColl->set((string) $author->getId(), $author);
        }

        return $authorColl;
    }

    public function getAuthorFromDto(AssetFileMediaApiCreateDecorator $dto): ?Author
    {
        return $this->getAuthor(
            title: $dto->getAuthor(),
            extSystem: $this->licenceProvider->getExtSystem($dto)
        );
    }

    private function getAuthor(string $title, ExtSystem $extSystem): ?Author
    {
        $title = StringHelper::parseString(input: $title, length: 255);
        if (empty($title)) {
            return null;
        }

        $author = $this->repository->findOneBy([
            'name' => $title,
            'extSystem' => $extSystem,
        ]);

        if ($author instanceof Author) {
            return $author;
        }

        return $this->authorManager->create(
            author: (new Author())
                ->setExtSystem($extSystem)
                ->setName($title),
            flush: false
        );
    }
}
