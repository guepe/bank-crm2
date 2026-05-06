<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260506170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add client trust fields and reusable user security tokens';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD COLUMN email_verified_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN consent_accepted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN consent_version VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN data_export_requested_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN data_deletion_requested_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE TABLE user_security_token (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, purpose VARCHAR(64) NOT NULL, token VARCHAR(128) NOT NULL, created_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, used_at DATETIME DEFAULT NULL, CONSTRAINT FK_EEBC7DFCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_EEBC7DFCA76ED395 ON user_security_token (user_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_security_token_token ON user_security_token (token)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user_security_token');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, username, email, roles, password, enabled, contact_id FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, email VARCHAR(180) DEFAULT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, enabled BOOLEAN DEFAULT 1 NOT NULL, contact_id INTEGER DEFAULT NULL, CONSTRAINT FK_8D93D649E7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user (id, username, email, roles, password, enabled, contact_id) SELECT id, username, email, roles, password, enabled, contact_id FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_username ON user (username)');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_email ON user (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7A1254A ON user (contact_id)');
    }
}
