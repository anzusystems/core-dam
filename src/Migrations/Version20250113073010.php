<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250113073010 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE distribution ADD ext_system_id INT DEFAULT NULL');
        $this->addSql('SET SESSION foreign_key_checks = OFF');
        $this->addSql('
            ALTER TABLE distribution
                ADD CONSTRAINT FK_A4483781E961F7A FOREIGN KEY (ext_system_id) REFERENCES ext_system (id),
                ALGORITHM = INPLACE,
                LOCK = NONE
        ');
        $this->addSql('SET SESSION foreign_key_checks = ON');
        $this->addSql('CREATE INDEX IDX_A4483781E961F7A ON distribution (ext_system_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE distribution DROP FOREIGN KEY FK_A4483781E961F7A');
        $this->addSql('DROP INDEX IDX_A4483781E961F7A ON distribution');
        $this->addSql('ALTER TABLE distribution DROP ext_system_id');
    }
}
