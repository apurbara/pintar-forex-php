<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231101123829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ClosingRequest (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', status VARCHAR(255) NOT NULL, transactionValue INT NOT NULL, note LONGTEXT DEFAULT NULL, AssignedCustomer_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_4F17BA906867A939 (AssignedCustomer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE RecycleRequest (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', status VARCHAR(255) NOT NULL, note LONGTEXT DEFAULT NULL, AssignedCustomer_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_AF6BC7556867A939 (AssignedCustomer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ClosingRequest ADD CONSTRAINT FK_4F17BA906867A939 FOREIGN KEY (AssignedCustomer_id) REFERENCES AssignedCustomer (id)');
        $this->addSql('ALTER TABLE RecycleRequest ADD CONSTRAINT FK_AF6BC7556867A939 FOREIGN KEY (AssignedCustomer_id) REFERENCES AssignedCustomer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ClosingRequest DROP FOREIGN KEY FK_4F17BA906867A939');
        $this->addSql('ALTER TABLE RecycleRequest DROP FOREIGN KEY FK_AF6BC7556867A939');
        $this->addSql('DROP TABLE ClosingRequest');
        $this->addSql('DROP TABLE RecycleRequest');
    }
}
