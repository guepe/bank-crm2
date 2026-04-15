<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260415180608 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE onboarding_session (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, status VARCHAR(50) NOT NULL, messages CLOB NOT NULL, extracted_data CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INTEGER NOT NULL, account_id INTEGER DEFAULT NULL, contact_id INTEGER DEFAULT NULL, CONSTRAINT FK_118C4943A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_118C49439B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_118C4943E7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_118C4943A76ED395 ON onboarding_session (user_id)');
        $this->addSql('CREATE INDEX IDX_118C49439B6B5FBA ON onboarding_session (account_id)');
        $this->addSql('CREATE INDEX IDX_118C4943E7A1254A ON onboarding_session (contact_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE onboarding_session');
    }
}
