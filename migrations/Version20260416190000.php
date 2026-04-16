<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260416190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a workflow status to leads';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE lead ADD status VARCHAR(30) DEFAULT 'new' NOT NULL");
        $this->addSql("UPDATE lead SET status = 'new' WHERE status = ''");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lead DROP status');
    }
}
