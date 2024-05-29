<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240523032237 extends AbstractMigration
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
        $this->addSql('DROP INDEX IDX_4F17BA906867A939 ON ClosingRequest');
        $this->addSql('ALTER TABLE ClosingRequest CHANGE AssignedCustomer_id CustomerAssignment_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE ClosingRequest ADD CONSTRAINT FK_4F17BA90A1A6CD65 FOREIGN KEY (CustomerAssignment_id) REFERENCES CustomerAssignment (id)');
        $this->addSql('CREATE INDEX IDX_4F17BA90A1A6CD65 ON ClosingRequest (CustomerAssignment_id)');
        $this->addSql('DROP INDEX IDX_AF6BC7556867A939 ON RecycleRequest');
        $this->addSql('ALTER TABLE RecycleRequest CHANGE AssignedCustomer_id CustomerAssignment_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE RecycleRequest ADD CONSTRAINT FK_AF6BC755A1A6CD65 FOREIGN KEY (CustomerAssignment_id) REFERENCES CustomerAssignment (id)');
        $this->addSql('CREATE INDEX IDX_AF6BC755A1A6CD65 ON RecycleRequest (CustomerAssignment_id)');
        $this->addSql('DROP INDEX IDX_5CF6B12D6867A939 ON SalesActivitySchedule');
        $this->addSql('ALTER TABLE SalesActivitySchedule CHANGE AssignedCustomer_id CustomerAssignment_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE SalesActivitySchedule ADD CONSTRAINT FK_5CF6B12DA1A6CD65 FOREIGN KEY (CustomerAssignment_id) REFERENCES CustomerAssignment (id)');
        $this->addSql('CREATE INDEX IDX_5CF6B12DA1A6CD65 ON SalesActivitySchedule (CustomerAssignment_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE VerificationReport (id CHAR(36) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', note LONGTEXT CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, Customer_id CHAR(36) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'(DC2Type:guid)\', CustomerVerification_id CHAR(36) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'(DC2Type:guid)\', INDEX IDX_8A06874ED9179049 (CustomerVerification_id), INDEX IDX_8A06874E15094C24 (Customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE VerificationReport ADD CONSTRAINT FK_8A06874E15094C24 FOREIGN KEY (Customer_id) REFERENCES Customer (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE VerificationReport ADD CONSTRAINT FK_8A06874ED9179049 FOREIGN KEY (CustomerVerification_id) REFERENCES CustomerVerification (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE ClosingRequest DROP FOREIGN KEY FK_4F17BA90A1A6CD65');
        $this->addSql('DROP INDEX IDX_4F17BA90A1A6CD65 ON ClosingRequest');
        $this->addSql('ALTER TABLE ClosingRequest CHANGE CustomerAssignment_id AssignedCustomer_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('CREATE INDEX IDX_4F17BA906867A939 ON ClosingRequest (AssignedCustomer_id)');
        $this->addSql('ALTER TABLE RecycleRequest DROP FOREIGN KEY FK_AF6BC755A1A6CD65');
        $this->addSql('DROP INDEX IDX_AF6BC755A1A6CD65 ON RecycleRequest');
        $this->addSql('ALTER TABLE RecycleRequest CHANGE CustomerAssignment_id AssignedCustomer_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('CREATE INDEX IDX_AF6BC7556867A939 ON RecycleRequest (AssignedCustomer_id)');
        $this->addSql('ALTER TABLE SalesActivitySchedule DROP FOREIGN KEY FK_5CF6B12DA1A6CD65');
        $this->addSql('DROP INDEX IDX_5CF6B12DA1A6CD65 ON SalesActivitySchedule');
        $this->addSql('ALTER TABLE SalesActivitySchedule CHANGE CustomerAssignment_id AssignedCustomer_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('CREATE INDEX IDX_5CF6B12D6867A939 ON SalesActivitySchedule (AssignedCustomer_id)');
    }
}
