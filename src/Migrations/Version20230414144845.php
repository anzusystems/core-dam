<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230414144845 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            UPDATE custom_form_element cfe 
            INNER JOIN custom_form cf on cfe.form_id = cf.id 
            SET cfe.attributes_max_value = 255 
            WHERE cf.ext_system_id = 4 AND cfe.key_name = 'author' 
            LIMIT 1;
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            UPDATE custom_form_element cfe 
            INNER JOIN custom_form cf on cfe.form_id = cf.id 
            SET cfe.attributes_max_value = 64 
            WHERE cf.ext_system_id = 4 AND cfe.key_name = 'author' 
            LIMIT 1;
        ");
    }
}
