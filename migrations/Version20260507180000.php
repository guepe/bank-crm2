<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260507180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'US036/037 — Table prescriber_invitation pour le partage prescripteur avec blocs autorises et corrections tracees.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE prescriber_invitation (
                id               INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                session_id       INTEGER NOT NULL REFERENCES onboarding_session(id) ON DELETE CASCADE,
                token            VARCHAR(128) NOT NULL UNIQUE,
                prescriber_role  VARCHAR(50)  NOT NULL,
                authorized_blocks CLOB NOT NULL,
                note             CLOB DEFAULT NULL,
                expires_at       DATETIME NOT NULL,
                created_at       DATETIME NOT NULL,
                accessed_at      DATETIME DEFAULT NULL,
                revoked_at       DATETIME DEFAULT NULL,
                correction_count INTEGER NOT NULL DEFAULT 0
            )
        SQL);
        $this->addSql('CREATE INDEX idx_prescriber_token   ON prescriber_invitation (token)');
        $this->addSql('CREATE INDEX idx_prescriber_session ON prescriber_invitation (session_id, created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE prescriber_invitation');
    }
}
