<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251227143258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add custom_fields table with ordering and constraints';
    }

    public function up(Schema $schema): void
    {
        // таблица custom_fields
        $this->addSql(
            'CREATE TABLE custom_fields (
                id INT NOT NULL,
                inventory_id INT NOT NULL,
                type VARCHAR(20) NOT NULL,
                position INT NOT NULL,
                is_required BOOLEAN NOT NULL DEFAULT FALSE,
                PRIMARY KEY(id)
            )'
        );

        // FK → inventories
        $this->addSql(
            'ALTER TABLE custom_fields
             ADD CONSTRAINT FK_CUSTOM_FIELDS_INVENTORY
             FOREIGN KEY (inventory_id)
             REFERENCES inventories (id)
             ON DELETE CASCADE'
        );

        // индекс по inventory
        $this->addSql(
            'CREATE INDEX IDX_CUSTOM_FIELDS_INVENTORY
             ON custom_fields (inventory_id)'
        );

        // уникальность позиции внутри inventory
        $this->addSql(
            'CREATE UNIQUE INDEX UNIQ_CUSTOM_FIELDS_POSITION
             ON custom_fields (inventory_id, position)'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_CUSTOM_FIELDS_POSITION');
        $this->addSql('DROP INDEX IDX_CUSTOM_FIELDS_INVENTORY');
        $this->addSql('DROP TABLE custom_fields');
    }
}
