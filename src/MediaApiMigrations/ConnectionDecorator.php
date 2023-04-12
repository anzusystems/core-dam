<?php

declare(strict_types=1);

namespace App\MediaApiMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;

final class ConnectionDecorator
{
    private array $bulkCache = [];
    private array $updateCache = [];

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function prepareUpdate(string $table, array $data, array $criteria): void
    {
        if (false === isset($this->updateCache[$table])) {
            $this->updateCache[$table] = [];
        }

        $this->updateCache[$table][] = [
            'data' => $data,
            'criteria' => $criteria,
        ];
    }

    public function prepareBulkInsert(string $table, array $data, array $duplicateKeyUpdate = ['id = new_row.id']): void
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
    public function flush(): void
    {
        $this->connection->beginTransaction();

        foreach ($this->bulkCache as $table => $values) {
            $this->insertBulk($this->connection, $table, $values['data'], $values['duplicateUpdate']);
        }

        foreach ($this->updateCache as $table => $values) {
            foreach ($values as $update) {
                if (false === isset($update['data'], $update['criteria'])) {
                    continue;
                }

                $this->connection->update(
                    $table,
                    $update['data'],
                    $update['criteria'],
                );
            }
        }

        $this->bulkCache = [];
        $this->updateCache = [];
        $this->connection->commit();
    }

    /**
     * @throws Exception
     */
    private function insertBulk(Connection $connection, string $table, array $data, array $duplicateKeyUpdate): ?Result
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

        try {
        /** @psalm-suppress UndefinedInterfaceMethod */
            return $statement->executeQuery();
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    private function getRunnableSql(string $sql, array $params): string
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
