<?php

declare(strict_types=1);

namespace App\Migrations;

use App\Security\Permission\DamPermissions;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240523123210 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        foreach (DamPermissions::ALL as $permission) {
            $this->updatePermissionGroupReadToView($permission);
            $this->updateUserPermissionsReadToView($permission);
        }

        // add ROLE_SUPER_ADMIN to users with role ROLE_ADMIN
        $this->addSql("UPDATE user SET roles = JSON_ARRAY_APPEND(roles, '$', 'ROLE_SUPER_ADMIN') WHERE JSON_CONTAINS(roles, '\"ROLE_ADMIN\"');");
        // add ROLE_ADMIN to users with role ROLE_DAM_ADMIN
        $this->addSql("UPDATE user SET roles = JSON_ARRAY_APPEND(roles, '$', 'ROLE_ADMIN') WHERE JSON_CONTAINS(roles, '\"ROLE_DAM_ADMIN\"') AND JSON_CONTAINS(roles, '\"ROLE_ADMIN\"') = 0;");
        // remove ROLE_DAM_ADMIN from all users
        $this->addSql("UPDATE user SET roles = JSON_REMOVE(roles, JSON_UNQUOTE(JSON_SEARCH(roles, 'one', 'ROLE_DAM_ADMIN'))) WHERE JSON_CONTAINS(roles, '\"ROLE_DAM_ADMIN\"');");
    }

    public function down(Schema $schema): void
    {
        foreach (DamPermissions::ALL as $permission) {
            $this->revertPermissionGroupReadToView($permission);
            $this->revertUserPermissionReadToView($permission);
        }

        // add ROLE_DAM_ADMIN to users with role ROLE_ADMIN
        $this->addSql("UPDATE user SET roles = JSON_ARRAY_APPEND(roles, '$', 'ROLE_DAM_ADMIN') WHERE JSON_CONTAINS(roles, '\"ROLE_ADMIN\"');");
        // remove ROLE_ADMIN from all users
        $this->addSql("UPDATE user SET roles = JSON_REMOVE(roles, JSON_UNQUOTE(JSON_SEARCH(roles, 'one', 'ROLE_ADMIN'))) WHERE JSON_CONTAINS(roles, '\"ROLE_ADMIN\"');");

        // add ROLE_ADMIN to users with role ROLE_SUPER_ADMIN
        $this->addSql("UPDATE user SET roles = JSON_ARRAY_APPEND(roles, '$', 'ROLE_ADMIN') WHERE JSON_CONTAINS(roles, '\"ROLE_SUPER_ADMIN\"');");
        // remove ROLE_SUPER_ADMIN from all users
        $this->addSql("UPDATE user SET roles = JSON_REMOVE(roles, JSON_UNQUOTE(JSON_SEARCH(roles, 'one', 'ROLE_SUPER_ADMIN'))) WHERE JSON_CONTAINS(roles, '\"ROLE_SUPER_ADMIN\"');");
    }

    private function updateUserPermissionsReadToView(string $permission): void
    {
        if (false === str_ends_with($permission, '_read')) {
            return;
        }

        $oldPermName = str_replace('_read', '_view', $permission);
        $this->addSql("
            UPDATE user
            SET permissions = JSON_SET(permissions, '$.{$permission}', JSON_EXTRACT(permissions, '$.{$oldPermName}'))
            WHERE permissions->'$.{$oldPermName}' IS NOT NULL
        ");
    }

    private function updatePermissionGroupReadToView(string $permission): void
    {
        if (false === str_ends_with($permission, '_read')) {
            return;
        }

        $oldPermName = str_replace('_read', '_view', $permission);
        $this->addSql("
            UPDATE permission_group
            SET permissions = JSON_SET(permissions, '$.{$permission}', JSON_EXTRACT(permissions, '$.{$oldPermName}'))
            WHERE permissions->'$.{$oldPermName}' IS NOT NULL
        ");
    }

    private function revertPermissionGroupReadToView(string $permission): void
    {
        if (false === str_ends_with($permission, '_read')) {
            return;
        }

        $this->addSql("
            UPDATE permission_group
            SET permissions = JSON_REMOVE(permissions, '$.{$permission}')
            WHERE permissions->'$.{$permission}' IS NOT NULL
        ");
    }

    private function revertUserPermissionReadToView(string $permission): void
    {
        if (false === str_ends_with($permission, '_read')) {
            return;
        }

        $this->addSql("
            UPDATE user
            SET permissions = JSON_REMOVE(permissions, '$.{$permission}')
            WHERE permissions->'$.{$permission}' IS NOT NULL
        ");
    }
}
