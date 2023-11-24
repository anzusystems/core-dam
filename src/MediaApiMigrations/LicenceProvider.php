<?php

declare(strict_types=1);

namespace App\MediaApiMigrations;

use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Exception\RuntimeException;
use AnzuSystems\CoreDamBundle\Repository\AssetLicenceRepository;
use App\App;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final class LicenceProvider
{
    private array $cache = [];
    private ConnectionDecorator $connectionDecorator;

    public function __construct(
        private readonly Connection $defaultConnection,
        private readonly AssetLicenceRepository $licenceRepository,
    ) {
        $this->connectionDecorator = new ConnectionDecorator($this->defaultConnection);
    }

    public function getLicence(string $title, int $extSystemId): AssetLicence
    {
        $licence = $this->licenceRepository->find(100_000);
        if (null === $licence) {
            throw new RuntimeException('Licence not found');
        }

        return $licence;
    }

    /**
     * @throws Exception
     */
    public function getLicenceId(string $title, int $extSystemId): int
    {
        if (isset($this->cache[$extSystemId][$title])) {
            return $this->cache[$extSystemId][$title];
        }

        $id = $this->defaultConnection->fetchOne(
            'SELECT id FROM asset_licence WHERE name = :name AND ext_system_id = :ext_system_id',
            [
                'name' => $title,
                'ext_system_id' => $extSystemId,
            ]
        );

        if (false === is_numeric($id)) {
            $id = $this->createLicence($title, $extSystemId);
        }

        if (false === isset($this->cache[$extSystemId])) {
            $this->cache[$extSystemId] = [];
        }

        $this->cache[$extSystemId][$title] = (int) $id;

        return $this->cache[$extSystemId][$title];
    }

    /**
     * @throws Exception
     */
    private function createLicence(string $title, int $extSystemId): int
    {
        $stockId = 1;
        $id = 300_000 + $stockId;

        // todo LICENCES
        $this->connectionDecorator->prepareBulkInsert(
            'asset_licence',
            [
                'id' => $id, // todo
                'name' => $title,
                'ext_id' => (string) $stockId,
                'ext_system_id' => $extSystemId,
                'created_by_id' => App::getUserIdConsole(),
                'modified_by_id' => App::getUserIdConsole(),
                'created_at' => App::getAppDate()->format(DateTimeInterface::ATOM),
                'modified_at' => App::getAppDate()->format(DateTimeInterface::ATOM),
            ],
            []
        );
        $this->connectionDecorator->flush();

        return $id;
    }
}
