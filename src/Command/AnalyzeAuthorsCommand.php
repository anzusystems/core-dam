<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CommonBundle\Csv\CsvHelper;
use AnzuSystems\Contracts\AnzuApp;
use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Entity\Author;
use AnzuSystems\CoreDamBundle\Repository\AssetFileRepository;
use AnzuSystems\CoreDamBundle\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:authors:analyze',
    description: 'Analyze authors'
)]
final class AnalyzeAuthorsCommand extends Command
{
    private const int LIMIT = 1_000;
    private const string EXIF_AUTHORS_FILE = 'exif_authors.csv';
    private const string ENTITY_AUTHORS_FILE = 'entity_authors.csv';
    private const string ARG_EXT_SYSTEM_ID = 'extSystemId';

    public function __construct(
        private readonly AssetFileRepository $assetFileRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly AuthorRepository $authorRepository,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addArgument(
            name: self::ARG_EXT_SYSTEM_ID,
            mode: InputArgument::OPTIONAL,
            default: 1
        );
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $extSystemId = (int) $input->getArgument(self::ARG_EXT_SYSTEM_ID);
        $this->analyzeAuthors($output, $extSystemId);
        $this->privateFunction($output, $extSystemId);

        return self::SUCCESS;
    }

    private function analyzeAuthors(OutputInterface $output, int $xtSystemId): void
    {
        $filePath = AnzuApp::getDataDir() . '/' . self::ENTITY_AUTHORS_FILE;
        CsvHelper::writeCsv($filePath);
        $csv = CsvHelper::appendCsv($filePath);

        $progressBar = new ProgressBar($output, $this->assetFileRepository->count());
        $progressBar->setFormat('debug');
        $lastId = '';

        do {
            $entities = $this->authorRepository->getAll($lastId);

            foreach ($entities as $entity) {
                $lastId = (string) $entity->getId();
                $progressBar->advance();

                /** @var Author $entity */
                if (false === ($xtSystemId === $entity->getExtSystem()->getId())) {
                    continue;
                }

                $csv->fputcsv([
                    $entity->getId(),
                    $entity->getName(),
                    $entity->getFlags()->isReviewed() ? 'true' : 'false',
                    $this->getAssetsCountByAuthor($entity),
                ]);
            }

            $this->entityManager->clear();
        } while (self::LIMIT === $entities->count());

        $progressBar->finish();
        $output->writeln('');
    }

    private function privateFunction(OutputInterface $output, int $xtSystemId): void
    {
        $progressBar = new ProgressBar($output, $this->assetFileRepository->count());
        $progressBar->setFormat('debug');
        $lastId = '';

        $authorsCache = [];

        do {
            $entities = $this->assetFileRepository->getAll(idFrom: $lastId, limit: self::LIMIT);

            /** @var AssetFile $entity */
            foreach ($entities as $entity) {
                $lastId = (string) $entity->getId();
                $progressBar->advance();

                if (false === ($xtSystemId === $entity->getExtSystem()->getId())) {
                    continue;
                }

                $metadta = $entity->getMetadata()->getExifData();
                $author = $metadta['Artist'] ?? $metadta['Owners'] ?? $metadta['Creator'] ?? $metadta['OwnerName'] ?? null;

                if (null === $author) {
                    continue;
                }

                if (false === isset($authorsCache[$author])) {
                    $authorsCache[$author] = 0;
                }

                $authorsCache[$author]++;
            }

            $this->entityManager->clear();
        } while (self::LIMIT === $entities->count());

        $progressBar->finish();
        $output->writeln(PHP_EOL . 'Writing file');

        $progressBar = new ProgressBar($output, count($authorsCache));
        $progressBar->setFormat('debug');

        $filePath = AnzuApp::getDataDir() . '/' . self::EXIF_AUTHORS_FILE;
        CsvHelper::writeCsv($filePath);
        $csv = CsvHelper::appendCsv($filePath);

        $csv->fputcsv(['Author', 'Count']);
        foreach ($authorsCache as $author => $count) {
            $csv->fputcsv([$author, $count]);
            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln('');
    }

    private function getAssetsCountByAuthor(Author $author): int
    {
        return (int) $this->entityManager
            ->getRepository(Asset::class)
            ->createQueryBuilder('entity')
            ->select('COUNT(entity)')
            ->innerJoin('entity.authors', 'author')
            ->where('author.id = :id')
            ->setParameter('id', $author->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }
}
