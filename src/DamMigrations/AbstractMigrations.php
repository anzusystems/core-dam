<?php

declare(strict_types=1);

namespace App\DamMigrations;

use AnzuSystems\AuthBundle\Exception\UnsuccessfulAccessTokenRequestException;
use AnzuSystems\AuthBundle\Exception\UnsuccessfulUserInfoRequestException;
use AnzuSystems\AuthBundle\HttpClient\OAuth2HttpClient;
use AnzuSystems\Contracts\AnzuApp;
use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use App\MediaApiMigrations\ConnectionDecorator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractMigrations
{
    use OutputUtilTrait;

    protected const BLOG_EXT_SYSTEM_ID = 4;

    protected Connection $damLegacyConnection;
    protected Connection $defaultConnection;
    protected Connection $blogConnection;
    protected ConnectionDecorator $defaultConnectionDecorator;
    protected OAuth2HttpClient $OAuth2HttpClient;
    private array $hasUserCache = [];

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
    public function setBlogConnection(Connection $blogConnection): void
    {
        $this->blogConnection = $blogConnection;
    }

    #[Required]
    public function setOAuth2HttpClient(OAuth2HttpClient $OAuth2HttpClient): void
    {
        $this->OAuth2HttpClient = $OAuth2HttpClient;
    }

    abstract public function migrate(): void;

    protected function getEmail(int $userId): ?string
    {
        $email = $this->blogConnection->fetchOne('SELECT email FROM user where id = ?', [$userId]);

        if (empty($email)) {
            try {
//                TODO remove and allow to resolve from central
//                return $this->OAuth2HttpClient->getSsoUserInfo((string) $userId)->getEmail();
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
        if (isset($this->hasUserCache[$userId])) {
            return $this->hasUserCache[$userId];
        }

        $res = $this->defaultConnection->fetchOne(
            'SELECT id FROM user WHERE id = ?',
            [
                $userId,
            ]
        );

        $this->hasUserCache[$userId] = is_int($res);

        return $this->hasUserCache[$userId];
    }

    protected function getUserIdWithFallback(int $userId): int
    {
        return $this->hasUser($userId) ? $userId : AnzuApp::getUserIdAnonymous();
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
