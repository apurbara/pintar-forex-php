<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240629135347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE VerificationReport DROP FOREIGN KEY FK_8A06874E15094C24');
        $this->addSql('ALTER TABLE VerificationReport DROP FOREIGN KEY FK_8A06874ED9179049');
        $this->addSql('DROP TABLE VerificationReport');
        $this->addSql('ALTER TABLE Customer DROP FOREIGN KEY FK_784FEC5F72B27900');
        $this->addSql('DROP INDEX IDX_784FEC5F72B27900 ON Customer');
        $this->addSql('ALTER TABLE Customer CHANGE Area_id City_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE Customer ADD CONSTRAINT FK_784FEC5F44115B33 FOREIGN KEY (City_id) REFERENCES City (id)');
        $this->addSql('CREATE INDEX IDX_784FEC5F44115B33 ON Customer (City_id)');
        $this->addSql('ALTER TABLE Sales DROP FOREIGN KEY FK_AA405F4072B27900');
        $this->addSql('ALTER TABLE Sales ADD CONSTRAINT FK_AA405F4072B27900 FOREIGN KEY (Area_id) REFERENCES City (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE VerificationReport (id CHAR(36) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', note LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, Customer_id CHAR(36) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'(DC2Type:guid)\', CustomerVerification_id CHAR(36) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'(DC2Type:guid)\', INDEX IDX_8A06874E15094C24 (Customer_id), INDEX IDX_8A06874ED9179049 (CustomerVerification_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE VerificationReport ADD CONSTRAINT FK_8A06874E15094C24 FOREIGN KEY (Customer_id) REFERENCES Customer (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VerificationReport ADD CONSTRAINT FK_8A06874ED9179049 FOREIGN KEY (CustomerVerification_id) REFERENCES CustomerVerification (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE Customer DROP FOREIGN KEY FK_784FEC5F44115B33');
        $this->addSql('DROP INDEX IDX_784FEC5F44115B33 ON Customer');
        $this->addSql('ALTER TABLE Customer CHANGE City_id Area_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE Customer ADD CONSTRAINT FK_784FEC5F72B27900 FOREIGN KEY (Area_id) REFERENCES Area (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_784FEC5F72B27900 ON Customer (Area_id)');
        $this->addSql('ALTER TABLE Sales DROP FOREIGN KEY FK_AA405F4072B27900');
        $this->addSql('ALTER TABLE Sales ADD CONSTRAINT FK_AA405F4072B27900 FOREIGN KEY (Area_id) REFERENCES Area (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
