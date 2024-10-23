<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241023115726 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            update custom_form_element efe
            inner join custom_form cf ON efe.form_id = cf.id
            set efe.position = 3
            where cf.ext_system_id = 1 and cf.asset_type = 'image' and efe.property = 'title'
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            update custom_form_element efe
            inner join custom_form cf ON efe.form_id = cf.id
            set efe.position = 1
            where cf.ext_system_id = 1 and cf.asset_type = 'image' and efe.property = 'title'
        ");

    }
}
