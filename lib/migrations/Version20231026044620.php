<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231026044620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE AssignedCustomer (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', Sales_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', Customer_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_11A0CED05D244851 (Sales_id), INDEX IDX_11A0CED015094C24 (Customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Customer (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, Area_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_784FEC5F72B27900 (Area_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE AssignedCustomer ADD CONSTRAINT FK_11A0CED05D244851 FOREIGN KEY (Sales_id) REFERENCES Sales (id)');
        $this->addSql('ALTER TABLE AssignedCustomer ADD CONSTRAINT FK_11A0CED015094C24 FOREIGN KEY (Customer_id) REFERENCES Customer (id)');
        $this->addSql('ALTER TABLE Customer ADD CONSTRAINT FK_784FEC5F72B27900 FOREIGN KEY (Area_id) REFERENCES Area (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE AssignedCustomer DROP FOREIGN KEY FK_11A0CED05D244851');
        $this->addSql('ALTER TABLE AssignedCustomer DROP FOREIGN KEY FK_11A0CED015094C24');
        $this->addSql('ALTER TABLE Customer DROP FOREIGN KEY FK_784FEC5F72B27900');
        $this->addSql('DROP TABLE AssignedCustomer');
        $this->addSql('DROP TABLE Customer');
    }
}
