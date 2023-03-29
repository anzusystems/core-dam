<?php

declare(strict_types=1);

namespace App\DamMigrations;

use AnzuSystems\AuthBundle\Exception\UnsuccessfulAccessTokenRequestException;
use AnzuSystems\AuthBundle\Exception\UnsuccessfulUserInfoRequestException;
use AnzuSystems\AuthBundle\HttpClient\OAuth2HttpClient;
use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use App\MediaApiMigrations\ConnectionDecorator;
use App\Model\MigrateConfig;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractMigrations
{
    use OutputUtilTrait;

    protected const CMS_LICENCE_ID = 100_000;
    protected const TOOLS_LICENCE_ID = 200_000;
    protected const BLOG_EXT_SYSTEM_ID = 4;
    protected const CMS_EXT_SYSTEM_ID = 1;
    protected const TOOLS_EXT_SYSTEM_ID = 1_000;

    protected Connection $damLegacyConnection;
    protected Connection $defaultConnection;
    protected Connection $artemisConnection;
    protected Connection $coreConnection;
    protected Connection $blogConnection;
    protected ConnectionDecorator $defaultConnectionDecorator;
    protected OAuth2HttpClient $OAuth2HttpClient;
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
        $this->defaultConnectionDecorator = new ConnectionDecorator($defaultConnection);
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
        $this->defaultConnectionDecorator->prepareBulkInsert($table, $data, $duplicateKeyUpdate);
    }

    /**
     * @throws Exception
     */
    protected function flush(): void
    {
        $this->defaultConnectionDecorator->flush();
    }
}
