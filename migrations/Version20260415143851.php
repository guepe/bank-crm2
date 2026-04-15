<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260415143851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, username, email, roles, password, enabled FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, email VARCHAR(180) DEFAULT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, enabled BOOLEAN DEFAULT 1 NOT NULL, contact_id INTEGER DEFAULT NULL, CONSTRAINT FK_8D93D649E7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO user (id, username, email, roles, password, enabled) SELECT id, username, email, roles, password, enabled FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_email ON user (email)');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_username ON user (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7A1254A ON user (contact_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, username, email, roles, password, enabled FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, email VARCHAR(180) DEFAULT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, enabled BOOLEAN DEFAULT 1 NOT NULL)');
        $this->addSql('INSERT INTO user (id, username, email, roles, password, enabled) SELECT id, username, email, roles, password, enabled FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_username ON user (username)');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_email ON user (email)');
    }
}
