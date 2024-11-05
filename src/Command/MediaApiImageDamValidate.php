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
    name: 'anzu:media-api:validate-dam',
    description: 'Prepare images to delete'
)]
final class MediaApiImageDamValidate extends Command
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
        $progress = $this->outputUtil->createProgressBar((int) $this->mediaApiConnection->executeQuery(
            'SELECT count(*) FROM mediaapi_image img WHERE img.anzu_dam_uuid is not null'
        )->fetchOne());

        $progress->setFormat('debug');
        $progress->start();

        $count = 0;
        $lastId = 0;
        do {
            $items = $this->selectFromDam($lastId);
            foreach ($items as $item) {
                $lastId = $item['id_image'];

                $res = $this->defaultConnection->executeQuery(
                    'SELECT img.id, img.asset_attributes_status FROM asset_file img WHERE img.id = :anzu_id',
                    ['anzu_id' => Uuid::fromBinary($item['anzu_dam_uuid'])->toString()]
                )->fetchAllAssociative()[0] ?? null;

                if (null === $res) {
                    $count++;
                    $this->outputUtil->writeln(
                        'Dam missing: ' . $item['id_image'] . ' -> ' . Uuid::fromBinary($item['anzu_dam_uuid'])->toString()
                    );
                }
                $progress->advance();
            }
        } while (self::LIMIT === count($items));

        $progress->finish();
        $this->outputUtil->writeln(PHP_EOL);
        $output->writeln('Missing images count: ' . $count);

        return Command::SUCCESS;
    }

    private function selectFromDam(int $lastId): array
    {
        if (null === $this->selectToDeleteStatement) {
            $this->selectToDeleteStatement = $this->mediaApiConnection->prepare(
                '
                SELECT img.id_image, img.anzu_dam_uuid, img.id_stock FROM mediaapi_image img 
                WHERE img.anzu_dam_uuid is not null AND img.id_image > :lastId AND img.id_status = 1 
                ORDER BY img.id_image ASC 
                LIMIT ' . self::LIMIT
            );
        }

        $this->selectToDeleteStatement->bindValue(':lastId', $lastId);

        return $this->selectToDeleteStatement->executeQuery()->fetchAllAssociative();
    }
}
