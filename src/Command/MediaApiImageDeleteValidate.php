<?php

declare(strict_types=1);

namespace App\Command;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use App\MediaApiMigrations\ConnectionDecorator;
use App\MediaApiMigrations\MediaApiPrepareImageDelete;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Exception;
use League\Flysystem\FilesystemException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;

#[AsCommand(
    name: 'anzu:media-api:validate-delete',
    description: 'Prepare images to delete'
)]
final class MediaApiImageDeleteValidate extends Command
{
    use OutputUtilTrait;

    public const int LIMIT = 1_000;

    private readonly ConnectionDecorator $damMediaApiMigConnectionDecorator;
    private ?Statement $selectToDeleteStatement = null;

    public function __construct(
        private readonly MediaApiPrepareImageDelete $apiImageDelete,
        private readonly Connection $mediaApiConnection,
        private readonly Connection $defaultConnection,
        private readonly Connection $damMediaApiMigConnection,
    ) {
        parent::__construct();
        $this->damMediaApiMigConnectionDecorator = new ConnectionDecorator($damMediaApiMigConnection);
    }

    /**
     * @throws Exception
     * @throws FilesystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $progress = $this->outputUtil->createProgressBar((int) $this->damMediaApiMigConnection->executeQuery(
            'SELECT count(*) FROM dam_image_media_api_drop'
        )->fetchOne());

        $progress->setFormat('debug');
        $progress->start();

        $lastId = 0;
        do {
            $items = $this->selectToDelete($lastId);
            foreach ($items as $item) {
                $lastId = $item['id'];

                $res = $this->mediaApiConnection->executeQuery(
                    'SELECT img.id_image FROM mediaapi_image img WHERE img.anzu_dam_uuid = :anzu_id',
                    ['anzu_id' => Uuid::fromString($item['asset_id'])->toBinary()]
                )->fetchFirstColumn();

                if (false === empty($res)) {
                    $this->outputUtil->writeln('Do not delete! ' . $item['asset_id']);
                }
                $progress->advance();
            }
        } while (self::LIMIT === count($items));

        $progress->finish();
        $this->outputUtil->writeln(PHP_EOL);

        return Command::SUCCESS;
    }

    private function selectToDelete(int $lastId): array
    {
        if (null === $this->selectToDeleteStatement) {
            $this->selectToDeleteStatement = $this->damMediaApiMigConnection->prepare(
                '
                SELECT id, media_api_id, main_file_id, asset_id, ext_slug FROM dam_image_media_api_drop
                WHERE id > :lastId
                ORDER BY id ASC 
                LIMIT ' . self::LIMIT
            );
        }

        $this->selectToDeleteStatement->bindValue(':lastId', $lastId);

        return $this->selectToDeleteStatement->executeQuery()->fetchAllAssociative();
    }
}
