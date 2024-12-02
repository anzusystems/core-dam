<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CommonBundle\Csv\CsvHelper;
use AnzuSystems\Contracts\AnzuApp;
use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Domain\Job\JobImageCopyFactory;
use AnzuSystems\CoreDamBundle\Entity\Author;
use AnzuSystems\CoreDamBundle\Repository\AssetFileRepository;
use AnzuSystems\CoreDamBundle\Repository\AssetLicenceRepository;
use AnzuSystems\CoreDamBundle\Repository\AssetRepository;
use AnzuSystems\CoreDamBundle\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Flysystem\FilesystemException;
use SplFileObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anzu:author:populate-hierarchy',
    description: 'Populates the current authors for an author'
)]
final class PopulateAuthorTreeHierarchyCommand extends Command
{
    use OutputUtilTrait;

    private const int SOURCE_AUTHOR_INDEX = 0;
    private const int TARGET_AUTHOR_INDEX = 4;
    private const int LIMIT = 100;

    private const string AUTHORS_CSV_OPT = 'file';
    private const string AUTHORS_CSV = 'entity_authors.csv';

    public function __construct(
        private readonly Connection $damMediaApiMigConnection,
        private readonly Connection $defaultConnection,
        private readonly JobImageCopyFactory $imageCopyFactory,
        private readonly AssetRepository $assetRepository,
        private readonly AssetLicenceRepository $assetLicenceRepository,
        private readonly AssetFileRepository $assetFileRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly AuthorRepository $authorRepository,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->addOption(
                name: self::AUTHORS_CSV_OPT,
                mode: InputOption::VALUE_REQUIRED,
                default: AnzuApp::getDataDir() . '/' . self::AUTHORS_CSV
            );
    }

    /**
     * @throws Exception
     * @throws FilesystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getOption(self::AUTHORS_CSV_OPT);
        if (false === file_exists($filePath)) {
            $output->writeln("<error>File not found at path: ({$filePath})</error>");

            return Command::FAILURE;
        }

        $csv = CsvHelper::getCsv($filePath);
        $csv->setFlags(SplFileObject::SKIP_EMPTY);
        $progress = new ProgressBar($output, CsvHelper::getTotalCount($csv));
        $progress->setFormat('debug');
        $progress->start();

        $i = 0;
        while (false === $csv->eof()) {
            $row = $csv->fgetcsv();
            $sourceAuthorId = (string) ($row[self::SOURCE_AUTHOR_INDEX] ?? '');
            $targetAuthorId = (string) ($row[self::TARGET_AUTHOR_INDEX] ?? '');

            if ($this->setupCurrentAuthors($sourceAuthorId, $targetAuthorId)) {
                $i++;
            }

            if (0 === ($i % self::LIMIT)) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }

            $progress->advance();
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        $progress->finish();
        $output->writeln(PHP_EOL . 'Processed ' . $i . ' rows');

        return Command::SUCCESS;
    }

    private function setupCurrentAuthors(string $sourceAuthorId, string $targetAuthorId): bool
    {
        if ('' === $sourceAuthorId || '' === $targetAuthorId) {
            return false;
        }

        $targetAuthorIds = explode(',', $targetAuthorId);
        $sourceAuthor = $this->authorRepository->find($sourceAuthorId);
        if (null === $sourceAuthor) {
            return false;
        }
        /** @var Collection<int, Author> $currentAuthorColl */
        $currentAuthorColl = new ArrayCollection(array_values(
            array_filter(
                array_map(fn (string $id): ?Author => $this->authorRepository->find($id), $targetAuthorIds)
            )
        ));

        if (false === ($currentAuthorColl->count() === count($targetAuthorIds))) {
            $this->outputUtil->writeln('<error>Error: Target author ids do not match any authors for Author id ' . $sourceAuthorId . '</error>');

            return false;
        }

        foreach ($currentAuthorColl as $author) {
            if (false === $author->getCurrentAuthors()->isEmpty()) {
                $this->outputUtil->writeln('<error>Error: Author ' . $author->getId() . ' already has current authors, cannot be set to current author  ' . $sourceAuthorId . '</error>');

                return false;
            }
        }

        foreach ($currentAuthorColl as $author) {
            $author->addCurrentAuthor($author);
            $author->addChildAuthor($sourceAuthor);
        }

        return true;
    }
}
