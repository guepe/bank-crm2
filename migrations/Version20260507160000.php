<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260507160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add beta pilotage tenants, user suspensions and incidents';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tenant (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            name VARCHAR(120) NOT NULL,
            code VARCHAR(80) NOT NULL,
            plan VARCHAR(30) NOT NULL,
            status VARCHAR(30) NOT NULL,
            beta_contact_email VARCHAR(180) DEFAULT NULL,
            notes CLOB DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        )');
        $this->addSql('CREATE UNIQUE INDEX uniq_tenant_code ON tenant (code)');

        $this->addSql('ALTER TABLE "user" ADD COLUMN tenant_id INTEGER DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD COLUMN suspended_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD COLUMN suspension_reason CLOB DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_user_tenant ON "user" (tenant_id)');

        $this->addSql('CREATE TABLE beta_pilotage_incident (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            tenant_id INTEGER DEFAULT NULL,
            created_by_id INTEGER DEFAULT NULL,
            title VARCHAR(160) NOT NULL,
            category VARCHAR(40) NOT NULL,
            severity VARCHAR(30) NOT NULL,
            status VARCHAR(30) NOT NULL,
            summary CLOB DEFAULT NULL,
            resolution_notes CLOB DEFAULT NULL,
            created_at DATETIME NOT NULL,
            resolved_at DATETIME DEFAULT NULL,
            CONSTRAINT FK_beta_incident_tenant FOREIGN KEY (tenant_id) REFERENCES tenant (id) ON DELETE SET NULL,
            CONSTRAINT FK_beta_incident_created_by FOREIGN KEY (created_by_id) REFERENCES "user" (id) ON DELETE SET NULL
        )');
        $this->addSql('CREATE INDEX idx_beta_incident_status ON beta_pilotage_incident (status, created_at)');
        $this->addSql('CREATE INDEX IDX_beta_incident_tenant ON beta_pilotage_incident (tenant_id)');
        $this->addSql('CREATE INDEX IDX_beta_incident_created_by ON beta_pilotage_incident (created_by_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE beta_pilotage_incident');
        $this->addSql('DROP TABLE tenant');
        $this->addSql('DROP INDEX IDX_user_tenant');
        $this->addSql('ALTER TABLE "user" DROP COLUMN tenant_id');
        $this->addSql('ALTER TABLE "user" DROP COLUMN suspended_at');
        $this->addSql('ALTER TABLE "user" DROP COLUMN suspension_reason');
    }
}
