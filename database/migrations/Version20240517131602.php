<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240517131602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `Admin` (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', aSuperUser TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(60) DEFAULT NULL, resetPasswordToken VARCHAR(64) DEFAULT NULL, resetPasswordTokenExpiredTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', UNIQUE INDEX admin_mail_idx (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Area (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', name VARCHAR(255) NOT NULL, description VARCHAR(1024) DEFAULT NULL, AreaStructure_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', Area_idOfParent CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_77A692565FBCB880 (AreaStructure_id), INDEX IDX_77A692565BF92604 (Area_idOfParent), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE AreaStructure (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', name VARCHAR(255) NOT NULL, description VARCHAR(1024) DEFAULT NULL, AreaStructure_idOfParent CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_B341E51461F3BD24 (AreaStructure_idOfParent), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE AssignedCustomer (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', status VARCHAR(255) NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', Sales_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', Customer_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CustomerJourney_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_11A0CED05D244851 (Sales_id), INDEX IDX_11A0CED015094C24 (Customer_id), INDEX IDX_11A0CED021B36B88 (CustomerJourney_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ClosingRequest (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', status VARCHAR(255) NOT NULL, transactionValue INT NOT NULL, note LONGTEXT DEFAULT NULL, AssignedCustomer_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_4F17BA906867A939 (AssignedCustomer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Customer (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) NOT NULL, source VARCHAR(255) DEFAULT NULL, Area_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_784FEC5F72B27900 (Area_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE CustomerJourney (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', initial TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(1024) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE CustomerVerification (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', weight SMALLINT DEFAULT NULL, position SMALLINT DEFAULT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(1024) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Manager (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, Personnel_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_35991C254214B8D (Personnel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Personnel (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(60) DEFAULT NULL, resetPasswordToken VARCHAR(64) DEFAULT NULL, resetPasswordTokenExpiredTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', UNIQUE INDEX personnel_mail_idx (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE RecycleRequest (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', concludedTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', status VARCHAR(255) NOT NULL, note LONGTEXT DEFAULT NULL, remark LONGTEXT DEFAULT NULL, AssignedCustomer_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_AF6BC7556867A939 (AssignedCustomer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Sales (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, type VARCHAR(255) NOT NULL, Manager_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', Personnel_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', Area_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_AA405F40376337B3 (Manager_id), INDEX IDX_AA405F404214B8D (Personnel_id), INDEX IDX_AA405F4072B27900 (Area_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SalesActivity (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', duration SMALLINT DEFAULT 0 NOT NULL, initial TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(1024) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SalesActivityReport (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', submitTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', content LONGTEXT DEFAULT NULL, SalesActivitySchedule_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_D62F90CCFFD4D80F (SalesActivitySchedule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SalesActivitySchedule (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', status VARCHAR(255) NOT NULL, startTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', endTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', AssignedCustomer_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', SalesActivity_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_5CF6B12D6867A939 (AssignedCustomer_id), INDEX IDX_5CF6B12D7C14D328 (SalesActivity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE VerificationReport (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', note LONGTEXT DEFAULT NULL, Customer_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CustomerVerification_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_8A06874E15094C24 (Customer_id), INDEX IDX_8A06874ED9179049 (CustomerVerification_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Area ADD CONSTRAINT FK_77A692565FBCB880 FOREIGN KEY (AreaStructure_id) REFERENCES AreaStructure (id)');
        $this->addSql('ALTER TABLE Area ADD CONSTRAINT FK_77A692565BF92604 FOREIGN KEY (Area_idOfParent) REFERENCES Area (id)');
        $this->addSql('ALTER TABLE AreaStructure ADD CONSTRAINT FK_B341E51461F3BD24 FOREIGN KEY (AreaStructure_idOfParent) REFERENCES AreaStructure (id)');
        $this->addSql('ALTER TABLE AssignedCustomer ADD CONSTRAINT FK_11A0CED05D244851 FOREIGN KEY (Sales_id) REFERENCES Sales (id)');
        $this->addSql('ALTER TABLE AssignedCustomer ADD CONSTRAINT FK_11A0CED015094C24 FOREIGN KEY (Customer_id) REFERENCES Customer (id)');
        $this->addSql('ALTER TABLE AssignedCustomer ADD CONSTRAINT FK_11A0CED021B36B88 FOREIGN KEY (CustomerJourney_id) REFERENCES CustomerJourney (id)');
        $this->addSql('ALTER TABLE ClosingRequest ADD CONSTRAINT FK_4F17BA906867A939 FOREIGN KEY (AssignedCustomer_id) REFERENCES AssignedCustomer (id)');
        $this->addSql('ALTER TABLE Customer ADD CONSTRAINT FK_784FEC5F72B27900 FOREIGN KEY (Area_id) REFERENCES Area (id)');
        $this->addSql('ALTER TABLE Manager ADD CONSTRAINT FK_35991C254214B8D FOREIGN KEY (Personnel_id) REFERENCES Personnel (id)');
        $this->addSql('ALTER TABLE RecycleRequest ADD CONSTRAINT FK_AF6BC7556867A939 FOREIGN KEY (AssignedCustomer_id) REFERENCES AssignedCustomer (id)');
        $this->addSql('ALTER TABLE Sales ADD CONSTRAINT FK_AA405F40376337B3 FOREIGN KEY (Manager_id) REFERENCES Manager (id)');
        $this->addSql('ALTER TABLE Sales ADD CONSTRAINT FK_AA405F404214B8D FOREIGN KEY (Personnel_id) REFERENCES Personnel (id)');
        $this->addSql('ALTER TABLE Sales ADD CONSTRAINT FK_AA405F4072B27900 FOREIGN KEY (Area_id) REFERENCES Area (id)');
        $this->addSql('ALTER TABLE SalesActivityReport ADD CONSTRAINT FK_D62F90CCFFD4D80F FOREIGN KEY (SalesActivitySchedule_id) REFERENCES SalesActivitySchedule (id)');
        $this->addSql('ALTER TABLE SalesActivitySchedule ADD CONSTRAINT FK_5CF6B12D6867A939 FOREIGN KEY (AssignedCustomer_id) REFERENCES AssignedCustomer (id)');
        $this->addSql('ALTER TABLE SalesActivitySchedule ADD CONSTRAINT FK_5CF6B12D7C14D328 FOREIGN KEY (SalesActivity_id) REFERENCES SalesActivity (id)');
        $this->addSql('ALTER TABLE VerificationReport ADD CONSTRAINT FK_8A06874E15094C24 FOREIGN KEY (Customer_id) REFERENCES Customer (id)');
        $this->addSql('ALTER TABLE VerificationReport ADD CONSTRAINT FK_8A06874ED9179049 FOREIGN KEY (CustomerVerification_id) REFERENCES CustomerVerification (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Area DROP FOREIGN KEY FK_77A692565FBCB880');
        $this->addSql('ALTER TABLE Area DROP FOREIGN KEY FK_77A692565BF92604');
        $this->addSql('ALTER TABLE AreaStructure DROP FOREIGN KEY FK_B341E51461F3BD24');
        $this->addSql('ALTER TABLE AssignedCustomer DROP FOREIGN KEY FK_11A0CED05D244851');
        $this->addSql('ALTER TABLE AssignedCustomer DROP FOREIGN KEY FK_11A0CED015094C24');
        $this->addSql('ALTER TABLE AssignedCustomer DROP FOREIGN KEY FK_11A0CED021B36B88');
        $this->addSql('ALTER TABLE ClosingRequest DROP FOREIGN KEY FK_4F17BA906867A939');
        $this->addSql('ALTER TABLE Customer DROP FOREIGN KEY FK_784FEC5F72B27900');
        $this->addSql('ALTER TABLE Manager DROP FOREIGN KEY FK_35991C254214B8D');
        $this->addSql('ALTER TABLE RecycleRequest DROP FOREIGN KEY FK_AF6BC7556867A939');
        $this->addSql('ALTER TABLE Sales DROP FOREIGN KEY FK_AA405F40376337B3');
        $this->addSql('ALTER TABLE Sales DROP FOREIGN KEY FK_AA405F404214B8D');
        $this->addSql('ALTER TABLE Sales DROP FOREIGN KEY FK_AA405F4072B27900');
        $this->addSql('ALTER TABLE SalesActivityReport DROP FOREIGN KEY FK_D62F90CCFFD4D80F');
        $this->addSql('ALTER TABLE SalesActivitySchedule DROP FOREIGN KEY FK_5CF6B12D6867A939');
        $this->addSql('ALTER TABLE SalesActivitySchedule DROP FOREIGN KEY FK_5CF6B12D7C14D328');
        $this->addSql('ALTER TABLE VerificationReport DROP FOREIGN KEY FK_8A06874E15094C24');
        $this->addSql('ALTER TABLE VerificationReport DROP FOREIGN KEY FK_8A06874ED9179049');
        $this->addSql('DROP TABLE `Admin`');
        $this->addSql('DROP TABLE Area');
        $this->addSql('DROP TABLE AreaStructure');
        $this->addSql('DROP TABLE AssignedCustomer');
        $this->addSql('DROP TABLE ClosingRequest');
        $this->addSql('DROP TABLE Customer');
        $this->addSql('DROP TABLE CustomerJourney');
        $this->addSql('DROP TABLE CustomerVerification');
        $this->addSql('DROP TABLE Manager');
        $this->addSql('DROP TABLE Personnel');
        $this->addSql('DROP TABLE RecycleRequest');
        $this->addSql('DROP TABLE Sales');
        $this->addSql('DROP TABLE SalesActivity');
        $this->addSql('DROP TABLE SalesActivityReport');
        $this->addSql('DROP TABLE SalesActivitySchedule');
        $this->addSql('DROP TABLE VerificationReport');
    }
}
