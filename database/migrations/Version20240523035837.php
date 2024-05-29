<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240523035837 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE VerificationReport (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', note LONGTEXT DEFAULT NULL, Customer_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CustomerVerification_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_8A06874E15094C24 (Customer_id), INDEX IDX_8A06874ED9179049 (CustomerVerification_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE VerificationReport ADD CONSTRAINT FK_8A06874E15094C24 FOREIGN KEY (Customer_id) REFERENCES Customer (id)');
        $this->addSql('ALTER TABLE VerificationReport ADD CONSTRAINT FK_8A06874ED9179049 FOREIGN KEY (CustomerVerification_id) REFERENCES CustomerVerification (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE VerificationReport DROP FOREIGN KEY FK_8A06874E15094C24');
        $this->addSql('ALTER TABLE VerificationReport DROP FOREIGN KEY FK_8A06874ED9179049');
        $this->addSql('DROP TABLE VerificationReport');
    }
}
