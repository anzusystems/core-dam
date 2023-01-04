<?php

declare(strict_types=1);


namespace App\DamMigrations;

use Doctrine\DBAL\Connection;
use RuntimeException;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractMigrations
{
    protected readonly Connection $damLegacyConnection;
    protected readonly Connection $defaultConnection;
    protected readonly Connection $artemisConnection;
    protected readonly Connection $coreConnection;
    protected readonly Connection $blogConnection;

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

    protected function getUserIdBySsoId(int $ssoId): int
    {
        $id = $this->defaultConnection->fetchOne(
            'SELECT * FROM user where sso_id = ?', [$ssoId]
        );

        if (false === $id) {
            throw new RuntimeException(sprintf('User ssoId (%s) missing', $ssoId));
        }

        return (int) $id;
    }
}