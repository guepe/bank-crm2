<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260416153000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add portal access links for secure client activation emails';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE portal_access_link (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, contact_id INTEGER NOT NULL, token VARCHAR(128) NOT NULL, summary_snapshot CLOB NOT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, sent_at DATETIME DEFAULT NULL, used_at DATETIME DEFAULT NULL, revoked_at DATETIME DEFAULT NULL, CONSTRAINT FK_F9334298A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F9334298E7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX uniq_portal_access_link_token ON portal_access_link (token)');
        $this->addSql('CREATE INDEX IDX_F9334298A76ED395 ON portal_access_link (user_id)');
        $this->addSql('CREATE INDEX IDX_F9334298E7A1254A ON portal_access_link (contact_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE portal_access_link');
    }
}
