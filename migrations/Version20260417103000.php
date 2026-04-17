<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260417103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Link documents to products through metaproduct';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE metaproduct_document (metaproduct_id INTEGER NOT NULL, document_id INTEGER NOT NULL, PRIMARY KEY(metaproduct_id, document_id), CONSTRAINT FK_1D361341A716FBE7 FOREIGN KEY (metaproduct_id) REFERENCES metaproduct (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1D361341C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_1D361341A716FBE7 ON metaproduct_document (metaproduct_id)');
        $this->addSql('CREATE INDEX IDX_1D361341C33F7837 ON metaproduct_document (document_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE metaproduct_document');
    }
}
