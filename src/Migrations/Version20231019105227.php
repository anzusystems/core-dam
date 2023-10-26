<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231019105227 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return true;
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'UPDATE custom_form_element 
                    SET attributes_readonly = 1 where property in (\'mediaApiIds\', \'mediaApiPaths\')
                '
        );
        $this->addSql(
            'UPDATE custom_form_element SET property = key_name'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'UPDATE custom_form_element 
                    SET attributes_readonly = 0 where property in (\'mediaApiIds\', \'mediaApiPaths\')  
                '
        );
    }
}
