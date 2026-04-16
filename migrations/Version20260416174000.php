<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260416174000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add bank relationships and secure bank access links';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE bank_relationship (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, contact_id INTEGER NOT NULL, bank_name VARCHAR(150) NOT NULL, bank_contact_name VARCHAR(150) DEFAULT NULL, bank_contact_email VARCHAR(180) DEFAULT NULL, bank_contact_phone VARCHAR(100) DEFAULT NULL, notes CLOB DEFAULT NULL, created_at DATETIME NOT NULL, CONSTRAINT FK_E63D7A7AE7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_E63D7A7AE7A1254A ON bank_relationship (contact_id)');
        $this->addSql('CREATE TABLE bank_access_link (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, bank_relationship_id INTEGER NOT NULL, contact_id INTEGER NOT NULL, token VARCHAR(128) NOT NULL, summary_snapshot CLOB NOT NULL, created_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, sent_at DATETIME DEFAULT NULL, responded_at DATETIME DEFAULT NULL, revoked_at DATETIME DEFAULT NULL, CONSTRAINT FK_D42A7D1E5781E70D FOREIGN KEY (bank_relationship_id) REFERENCES bank_relationship (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D42A7D1EE7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX uniq_bank_access_link_token ON bank_access_link (token)');
        $this->addSql('CREATE INDEX IDX_D42A7D1E5781E70D ON bank_access_link (bank_relationship_id)');
        $this->addSql('CREATE INDEX IDX_D42A7D1EE7A1254A ON bank_access_link (contact_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE bank_access_link');
        $this->addSql('DROP TABLE bank_relationship');
    }
}
