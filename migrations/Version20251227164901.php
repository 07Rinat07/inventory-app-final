<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251227164901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'InventoryItemValue: store values of custom fields per item';
    }

    public function up(Schema $schema): void
    {
        // Sequence only for this table
        $this->addSql(
            'CREATE SEQUENCE inventory_item_values_id_seq INCREMENT BY 1 MINVALUE 1 START 1'
        );

        // Main table
        $this->addSql(
            'CREATE TABLE inventory_item_values (
                id INT NOT NULL,
                item_id INT NOT NULL,
                field_id INT NOT NULL,
                value VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY(id)
            )'
        );

        // Indexes
        $this->addSql(
            'CREATE INDEX IDX_INV_ITEM_VALUES_ITEM ON inventory_item_values (item_id)'
        );
        $this->addSql(
            'CREATE INDEX IDX_INV_ITEM_VALUES_FIELD ON inventory_item_values (field_id)'
        );

        // One value per field per item
        $this->addSql(
            'CREATE UNIQUE INDEX UNIQ_INV_ITEM_FIELD
             ON inventory_item_values (item_id, field_id)'
        );

        // Foreign keys
        $this->addSql(
            'ALTER TABLE inventory_item_values
             ADD CONSTRAINT FK_INV_ITEM_VALUES_ITEM
             FOREIGN KEY (item_id)
             REFERENCES inventory_items (id)
             ON DELETE CASCADE'
        );

        $this->addSql(
            'ALTER TABLE inventory_item_values
             ADD CONSTRAINT FK_INV_ITEM_VALUES_FIELD
             FOREIGN KEY (field_id)
             REFERENCES custom_fields (id)
             ON DELETE CASCADE'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE inventory_item_values DROP CONSTRAINT FK_INV_ITEM_VALUES_ITEM'
        );
        $this->addSql(
            'ALTER TABLE inventory_item_values DROP CONSTRAINT FK_INV_ITEM_VALUES_FIELD'
        );

        $this->addSql('DROP TABLE inventory_item_values');
        $this->addSql('DROP SEQUENCE inventory_item_values_id_seq CASCADE');
    }
}
