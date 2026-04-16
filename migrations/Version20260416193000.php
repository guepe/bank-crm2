<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260416193000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add creation timestamps to accounts, contacts and documents';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE account ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL");
        $this->addSql("ALTER TABLE contact ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL");
        $this->addSql("ALTER TABLE document ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE account DROP created_at');
        $this->addSql('ALTER TABLE contact DROP created_at');
        $this->addSql('ALTER TABLE document DROP created_at');
    }
}
