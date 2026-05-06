<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for EP03: Add field provenance and audit trail.
 * Creates field_edit table to track all modifications with source, author, reason.
 */
final class Version20260506200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create field_edit table for collaborative document provenance tracking (EP03)';
    }

    public function up(Schema $schema): void
    {
        // SQLite-compatible migration (no COMMENT syntax)
        $this->addSql('CREATE TABLE field_edit (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            session_id INTEGER NOT NULL,
            author_id INTEGER DEFAULT NULL,
            field_path VARCHAR(255) NOT NULL,
            old_value CLOB DEFAULT NULL,
            new_value CLOB NOT NULL,
            source VARCHAR(50) NOT NULL DEFAULT \'declared\',
            role VARCHAR(50) NOT NULL DEFAULT \'system\',
            reason CLOB DEFAULT NULL,
            created_at DATETIME NOT NULL,
            CONSTRAINT FK_field_edit_session FOREIGN KEY (session_id) REFERENCES onboarding_session (id) ON DELETE CASCADE,
            CONSTRAINT FK_field_edit_author FOREIGN KEY (author_id) REFERENCES "user" (id) ON DELETE SET NULL
        )');

        $this->addSql('CREATE INDEX idx_session_timeline ON field_edit (session_id, created_at)');
        $this->addSql('CREATE INDEX idx_session_field ON field_edit (session_id, field_path)');
        $this->addSql('CREATE INDEX idx_field_edit_author ON field_edit (author_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE field_edit');
    }
}
