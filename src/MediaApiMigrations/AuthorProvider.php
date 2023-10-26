<?php

declare(strict_types=1);

namespace App\MediaApiMigrations;

use AnzuSystems\CoreDamBundle\Helper\StringHelper;
use App\App;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\Uid\Uuid;

final class AuthorProvider
{
    private array $cache = [];
    private ConnectionDecorator $connectionDecorator;

    public function __construct(
        private readonly Connection $defaultConnection,
    ) {
        $this->connectionDecorator = new ConnectionDecorator($this->defaultConnection);
    }


    /**
     * @throws Exception
     */
    public function getAuthor(string $title, int $extSystemId): string
    {
        $title = StringHelper::parseString($title, 255);

        if (isset($this->cache[$extSystemId][$title])) {
            return $this->cache[$extSystemId][$title];
        }

        $id = $this->defaultConnection->fetchOne(
            'SELECT id FROM author WHERE name = :title',
            [
                'title' => $title,
                'ext_system_id' => $extSystemId,
            ]
        );

        if (false === is_string($id)) {
            $id = $this->createAuthor($title, $extSystemId);
        }

        if (false === isset($this->cache[$extSystemId])) {
            $this->cache[$extSystemId] = [];
        }

        $this->cache[$extSystemId][$title] = (string) $id;

        return $id;
    }

    /**
     * @throws Exception
     */
    private function createAuthor(string $title, int $extSystemId): string
    {
        $id = (string) Uuid::v6();
        $this->connectionDecorator->prepareBulkInsert(
            'author',
            [
                'id' => $id,
                'ext_system_id' => $extSystemId,
                'created_by_id' => App::getUserIdConsole(),
                'modified_by_id' => App::getUserIdConsole(),
                'name' => $title,
                'identifier' => $title,
                'created_at' => App::getAppDate()->format(DateTimeInterface::ATOM),
                'modified_at' => App::getAppDate()->format(DateTimeInterface::ATOM),
                'flags_reviewed' => 0,
                'type' => 'none',
            ]
        );
        $this->connectionDecorator->flush();

        return $id;
    }
}
