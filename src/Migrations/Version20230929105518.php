<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230929105518 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset ADD ext_system_id INT DEFAULT NULL');
        $this->addSql('SET SESSION foreign_key_checks = OFF');
        $this->addSql('
            ALTER TABLE asset 
                ADD CONSTRAINT FK_2AF5A5CE961F7A FOREIGN KEY (ext_system_id) REFERENCES ext_system (id),
                ALGORITHM = INPLACE,
                LOCK = NONE
        ');
        $this->addSql('SET SESSION foreign_key_checks = ON');
        $this->addSql('CREATE INDEX IDX_2AF5A5CE961F7A ON asset (ext_system_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset DROP FOREIGN KEY FK_2AF5A5CE961F7A');
        $this->addSql('DROP INDEX IDX_2AF5A5CE961F7A ON asset');
        $this->addSql('ALTER TABLE asset DROP ext_system_id');
    }
}
