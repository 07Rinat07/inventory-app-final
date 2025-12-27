<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251227160258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE custom_fields_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE inventories_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE inventory_access_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE inventory_id_format_part_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE inventory_items_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE inventory_sequence_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE users_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('DROP INDEX uniq_custom_fields_position');
        $this->addSql('ALTER TABLE custom_fields ALTER is_required DROP DEFAULT');
        $this->addSql('ALTER INDEX idx_custom_fields_inventory RENAME TO IDX_4A48378C9EEA759');
        $this->addSql('ALTER INDEX idx_inventory_owner RENAME TO IDX_936C863D7E3C61F9');
        $this->addSql('DROP INDEX uniq_inventory_format_position');
        $this->addSql('ALTER TABLE inventory_id_format_part ALTER type TYPE VARCHAR(255)');
        $this->addSql('ALTER INDEX idx_inventory_id_format_part_inventory RENAME TO IDX_AB6AE5249EEA759');
        $this->addSql('ALTER TABLE inventory_items ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('COMMENT ON COLUMN inventory_items.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER INDEX uniq_inventory_sequence RENAME TO UNIQ_E6CC6DBA9EEA759');
        $this->addSql('ALTER TABLE users ALTER roles DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE custom_fields_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE inventories_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE inventory_access_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE inventory_id_format_part_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE inventory_items_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE inventory_sequence_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE users_id_seq CASCADE');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER INDEX idx_936c863d7e3c61f9 RENAME TO idx_inventory_owner');
        $this->addSql('ALTER TABLE users ALTER roles SET DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE custom_fields ALTER is_required SET DEFAULT false');
        $this->addSql('CREATE UNIQUE INDEX uniq_custom_fields_position ON custom_fields (inventory_id, "position")');
        $this->addSql('ALTER INDEX idx_4a48378c9eea759 RENAME TO idx_custom_fields_inventory');
        $this->addSql('ALTER TABLE inventory_items DROP created_at');
        $this->addSql('ALTER INDEX uniq_e6cc6dba9eea759 RENAME TO uniq_inventory_sequence');
        $this->addSql('ALTER TABLE inventory_id_format_part ALTER type TYPE VARCHAR(10)');
        $this->addSql('CREATE UNIQUE INDEX uniq_inventory_format_position ON inventory_id_format_part (inventory_id, "position")');
        $this->addSql('ALTER INDEX idx_ab6ae5249eea759 RENAME TO idx_inventory_id_format_part_inventory');
    }
}
