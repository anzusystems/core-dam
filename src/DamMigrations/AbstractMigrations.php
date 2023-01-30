<?php

declare(strict_types=1);

namespace App\DamMigrations;

use AnzuSystems\AuthBundle\Exception\UnsuccessfulAccessTokenRequestException;
use AnzuSystems\AuthBundle\Exception\UnsuccessfulUserInfoRequestException;
use AnzuSystems\AuthBundle\HttpClient\OAuth2HttpClient;
use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use App\Model\MigrateConfig;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractMigrations
{
    use OutputUtilTrait;

    protected const CMS_LICENCE_ID = 100_000;
    protected const BLOG_EXT_SYSTEM_ID = 4;

    protected const CMS_EXT_SYSTEM_ID = 1;

    protected readonly Connection $damLegacyConnection;
    protected readonly Connection $defaultConnection;
    protected readonly Connection $artemisConnection;
    protected readonly Connection $coreConnection;
    protected readonly Connection $blogConnection;
    protected readonly OAuth2HttpClient $OAuth2HttpClient;
    protected array $bulkCache = [];

    #[Required]
    public function setArtemisConnection(Connection $artemisConnection): void
    {
        $this->artemisConnection = $artemisConnection;
    }

    #[Required]
    public function setDamLegacyConnection(Connection $damLegacyConnection): void
    {
        $this->damLegacyConnection = $damLegacyConnection;
    }

    #[Required]
    public function setDefaultConnection(Connection $defaultConnection): void
    {
        $this->defaultConnection = $defaultConnection;
    }

    #[Required]
    public function setCoreConnection(Connection $coreConnection): void
    {
        $this->coreConnection = $coreConnection;
    }

    #[Required]
    public function setBlogConnection(Connection $blogConnection): void
    {
        $this->blogConnection = $blogConnection;
    }

    #[Required]
    public function setOAuth2HttpClient(OAuth2HttpClient $OAuth2HttpClient): void
    {
        $this->OAuth2HttpClient = $OAuth2HttpClient;
    }

    abstract public function migrate(MigrateConfig $migrateConfig): void;

    protected function getEmail(int $userId): ?string
    {
        $email =
            $this->blogConnection->fetchOne('SELECT email FROM user where id = ?', [$userId])
            ?? $this->coreConnection->fetchOne('SELECT email FROM user where id = ?', [$userId]);

        if (empty($email)) {
            try {
                return $this->OAuth2HttpClient->getSsoUserInfo((string) $userId)->getEmail();
            } catch (UnsuccessfulAccessTokenRequestException | UnsuccessfulUserInfoRequestException) {
            }
        }

        if (is_string($email)) {
            return $email;
        }

        $this->outputUtil->error(sprintf('User id (%s) missing, using fake address', $userId));

        return 'dam-' . $userId . '@anzusystems.dev';
    }

    protected function hasUser(int $userId): bool
    {
        $res = $this->defaultConnection->fetchOne(
            'SELECT id FROM user WHERE id = ?',
            [
                $userId,
            ]
        );

        return is_int($res);
    }

    protected function prepareBulkInsert(string $table, array $data, array $duplicateKeyUpdate = ['id = new_row.id']): void
    {
        if (false === isset($this->bulkCache[$table])) {
            $this->bulkCache[$table] = [
                'duplicateUpdate' => $duplicateKeyUpdate,
                'data' => [],
            ];
        }

        $this->bulkCache[$table]['data'][] = $data;
    }

    /**
     * @throws Exception
     */
    protected function flush(): void
    {
        $this->defaultConnection->beginTransaction();

        foreach ($this->bulkCache as $table => $values) {
            $this->insertBulk($this->defaultConnection, $table, $values['data'], $values['duplicateUpdate']);
        }

        $this->bulkCache = [];
        $this->defaultConnection->commit();
    }

    protected function insertBulk(Connection $connection, string $table, array $data, array $duplicateKeyUpdate): ?Result
    {
        if (empty($data)) {
            return null;
        }

        $rows = [];
        $params = [];

        $i = 0;
        foreach ($data as $row) {
            $tokens = [];
            foreach ($row as $column => $value) {
                $key = ':' . $column . '_' . $i;
                $tokens[] = $key;
                $params[$key] = $value;
            }

            $rows[] = '(' . implode(', ', $tokens) . ')';
            $i++;
        }

        $sql = 'INSERT INTO %s (%s) VALUES %s AS new_row';
        if (false === empty($duplicateKeyUpdate)) {
            $sql .= ' ON DUPLICATE KEY UPDATE ' . implode(', ', $duplicateKeyUpdate);
        }
        $sql .= ';';

        $sql = sprintf(
            $sql,
            $table,
            implode(', ', array_keys($data[0])),
            implode(', ', $rows)
        );

        $statement = $connection->prepare($sql);

        foreach ($params as $name => $value) {
            $statement->bindValue($name, $value);
        }

        return $statement->executeQuery();
    }

    protected function getRunnableSql(string $sql, array $params): string
    {
        $runnableSql = $sql;
        foreach ($params as $name => $value) {
            if (is_string($value)) {
                $value = "'" . $value . "'";
            }

            $runnableSql = str_replace($name, (string) $value, $runnableSql);
        }

        return $runnableSql;
    }
}
