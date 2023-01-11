<?php

declare(strict_types=1);


namespace App\DamMigrations;

use App\Model\MigrateConfig;
use Doctrine\DBAL\Connection;
use RuntimeException;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractMigrations
{
    protected const CMS_LICENCE_ID = 100_000;
    protected const BLOG_EXT_SYSTEM_ID = 4;

    protected const CMS_EXT_SYSTEM_ID = 1;

    protected readonly Connection $damLegacyConnection;
    protected readonly Connection $defaultConnection;
    protected readonly Connection $artemisConnection;
    protected readonly Connection $coreConnection;
    protected readonly Connection $blogConnection;
    protected array $userIdBySsoIdCache = [];

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

    abstract public function migrate(MigrateConfig $migrateConfig): void;

    protected function getUserIdBySsoId(int $ssoId): int
    {
        if (isset($this->userIdBySsoIdCache[$ssoId])) {
            return $this->userIdBySsoIdCache[$ssoId];
        }

        $id = $this->defaultConnection->fetchOne(
            'SELECT * FROM user where sso_id = ?', [$ssoId]
        );

        if (false === $id) {
            throw new RuntimeException(sprintf('User ssoId (%s) missing', $ssoId));
        }

        $this->userIdBySsoIdCache[$ssoId] = (int) $id;

        return $this->userIdBySsoIdCache[$ssoId];
    }
}
