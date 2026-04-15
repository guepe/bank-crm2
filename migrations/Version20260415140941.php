<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260415140941 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL, street_num VARCHAR(100) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, zip VARCHAR(100) DEFAULT NULL, country VARCHAR(100) DEFAULT NULL, company_statut VARCHAR(100) DEFAULT NULL, other_bank VARCHAR(100) DEFAULT NULL, notes CLOB DEFAULT NULL, type VARCHAR(100) DEFAULT NULL, starting_date DATE DEFAULT NULL)');
        $this->addSql('CREATE TABLE accounts_contacts (account_id INTEGER NOT NULL, contact_id INTEGER NOT NULL, PRIMARY KEY (account_id, contact_id), CONSTRAINT FK_6A9B6B169B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6A9B6B16E7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6A9B6B169B6B5FBA ON accounts_contacts (account_id)');
        $this->addSql('CREATE INDEX IDX_6A9B6B16E7A1254A ON accounts_contacts (contact_id)');
        $this->addSql('CREATE TABLE accounts_document (account_id INTEGER NOT NULL, document_id INTEGER NOT NULL, PRIMARY KEY (account_id, document_id), CONSTRAINT FK_81B2F4139B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_81B2F413C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_81B2F4139B6B5FBA ON accounts_document (account_id)');
        $this->addSql('CREATE INDEX IDX_81B2F413C33F7837 ON accounts_document (document_id)');
        $this->addSql('CREATE TABLE account_meta_product (account_id INTEGER NOT NULL, meta_product_id INTEGER NOT NULL, PRIMARY KEY (account_id, meta_product_id), CONSTRAINT FK_3E90D099B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3E90D09E56FC4F7 FOREIGN KEY (meta_product_id) REFERENCES metaproduct (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_3E90D099B6B5FBA ON account_meta_product (account_id)');
        $this->addSql('CREATE INDEX IDX_3E90D09E56FC4F7 ON account_meta_product (meta_product_id)');
        $this->addSql('CREATE TABLE category (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, parent_id INTEGER DEFAULT NULL, CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_64C19C1727ACA70 ON category (parent_id)');
        $this->addSql('CREATE TABLE contact (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, titre VARCHAR(1) DEFAULT NULL, firstname VARCHAR(100) DEFAULT NULL, lastname VARCHAR(100) NOT NULL, street_num VARCHAR(100) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, zip VARCHAR(100) DEFAULT NULL, country VARCHAR(100) DEFAULT NULL, email VARCHAR(150) DEFAULT NULL, phone VARCHAR(100) DEFAULT NULL, phone2 VARCHAR(100) DEFAULT NULL, gsm VARCHAR(16) DEFAULT NULL, birthplace VARCHAR(100) DEFAULT NULL, birthdate DATE DEFAULT NULL, eid VARCHAR(100) DEFAULT NULL, niss VARCHAR(100) DEFAULT NULL, profession VARCHAR(100) DEFAULT NULL, marital_status INTEGER DEFAULT NULL, income_amount INTEGER DEFAULT NULL, income_recurence VARCHAR(100) DEFAULT NULL, income_date VARCHAR(255) DEFAULT NULL, charged_people INTEGER DEFAULT NULL)');
        $this->addSql('CREATE TABLE contacts_document (contact_id INTEGER NOT NULL, document_id INTEGER NOT NULL, PRIMARY KEY (contact_id, document_id), CONSTRAINT FK_CD390F33E7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CD390F33C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_CD390F33E7A1254A ON contacts_document (contact_id)');
        $this->addSql('CREATE INDEX IDX_CD390F33C33F7837 ON contacts_document (document_id)');
        $this->addSql('CREATE TABLE document (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) DEFAULT NULL, path VARCHAR(255) DEFAULT NULL, mime_type VARCHAR(120) DEFAULT NULL, size INTEGER DEFAULT NULL)');
        $this->addSql('CREATE TABLE filelinked (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(100) NOT NULL, name VARCHAR(100) NOT NULL, filedata BLOB DEFAULT NULL)');
        $this->addSql('CREATE TABLE lead (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL, street_num VARCHAR(100) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, zip VARCHAR(100) DEFAULT NULL, country VARCHAR(100) DEFAULT NULL, company_statut VARCHAR(100) DEFAULT NULL, other_bank VARCHAR(100) DEFAULT NULL, notes CLOB DEFAULT NULL, type VARCHAR(100) DEFAULT NULL, starting_date DATE DEFAULT NULL)');
        $this->addSql('CREATE TABLE metaproduct (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, number VARCHAR(100) DEFAULT NULL, type VARCHAR(100) DEFAULT NULL, notes CLOB DEFAULT NULL, description CLOB DEFAULT NULL, reference VARCHAR(100) DEFAULT NULL, company CLOB DEFAULT NULL, taux_interet NUMERIC(10, 2) DEFAULT NULL, discr VARCHAR(255) NOT NULL, amount NUMERIC(10, 4) DEFAULT NULL, garantee CLOB DEFAULT NULL, purpose CLOB DEFAULT NULL, variability VARCHAR(100) DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, duration NUMERIC(10, 2) DEFAULT NULL, recurrent_prime_amount NUMERIC(10, 4) DEFAULT NULL, payment_date VARCHAR(100) DEFAULT NULL, capital_terme NUMERIC(10, 4) DEFAULT NULL, payment_deadline VARCHAR(100) DEFAULT NULL, reserve NUMERIC(10, 4) DEFAULT NULL, reserve_date DATE DEFAULT NULL, prime_reccurence VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE TABLE meta_product_category (meta_product_id INTEGER NOT NULL, category_id INTEGER NOT NULL, PRIMARY KEY (meta_product_id, category_id), CONSTRAINT FK_BA9F26C9E56FC4F7 FOREIGN KEY (meta_product_id) REFERENCES metaproduct (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BA9F26C912469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_BA9F26C9E56FC4F7 ON meta_product_category (meta_product_id)');
        $this->addSql('CREATE INDEX IDX_BA9F26C912469DE2 ON meta_product_category (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE accounts_contacts');
        $this->addSql('DROP TABLE accounts_document');
        $this->addSql('DROP TABLE account_meta_product');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE contacts_document');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE filelinked');
        $this->addSql('DROP TABLE lead');
        $this->addSql('DROP TABLE metaproduct');
        $this->addSql('DROP TABLE meta_product_category');
    }
}
