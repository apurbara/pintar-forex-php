<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240702030051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `Admin` (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', aSuperUser TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(60) DEFAULT NULL, resetPasswordToken VARCHAR(64) DEFAULT NULL, resetPasswordTokenExpiredTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', UNIQUE INDEX admin_mail_idx (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE City (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) NOT NULL, Province_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_8D69AD0A6FDA9E9D (Province_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ClosingRequest (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', status VARCHAR(255) NOT NULL, transactionValue INT NOT NULL, note LONGTEXT DEFAULT NULL, remark LONGTEXT DEFAULT NULL, CustomerAssignment_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_4F17BA90A1A6CD65 (CustomerAssignment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE CommonSalesMetric (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', lastModifiedTime DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', name VARCHAR(255) NOT NULL, target INT DEFAULT NULL, metricType VARCHAR(255) NOT NULL, evaluationType VARCHAR(255) NOT NULL, recurrenceType VARCHAR(255) NOT NULL, recurrenceCount SMALLINT DEFAULT NULL, displaySchema VARCHAR(1024) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE CompanyMetric (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', lastModifiedTime DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', name VARCHAR(255) NOT NULL, target INT DEFAULT NULL, metricType VARCHAR(255) NOT NULL, evaluationType VARCHAR(255) NOT NULL, recurrenceType VARCHAR(255) NOT NULL, recurrenceCount SMALLINT DEFAULT NULL, displaySchema VARCHAR(1024) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Customer (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) NOT NULL, source VARCHAR(255) DEFAULT NULL, City_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_784FEC5F44115B33 (City_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE CustomerAssignment (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', status VARCHAR(255) NOT NULL, Sales_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', Customer_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CustomerJourney_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_C4BFA65D5D244851 (Sales_id), INDEX IDX_C4BFA65D15094C24 (Customer_id), INDEX IDX_C4BFA65D21B36B88 (CustomerJourney_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE CustomerJourney (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', initial TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(1024) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE CustomerVerification (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', weight SMALLINT DEFAULT NULL, position SMALLINT DEFAULT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(1024) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Manager (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', suspended TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(60) DEFAULT NULL, resetPasswordToken VARCHAR(64) DEFAULT NULL, resetPasswordTokenExpiredTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', UNIQUE INDEX manager_mail_idx (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Province (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE RecycleRequest (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', concludedTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', status VARCHAR(255) NOT NULL, note LONGTEXT DEFAULT NULL, remark LONGTEXT DEFAULT NULL, CustomerAssignment_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_AF6BC755A1A6CD65 (CustomerAssignment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Sales (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', contractTerminated TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', contractTerminatedTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(60) DEFAULT NULL, resetPasswordToken VARCHAR(64) DEFAULT NULL, resetPasswordTokenExpiredTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', Manager_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', City_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_AA405F40376337B3 (Manager_id), INDEX IDX_AA405F4044115B33 (City_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SalesActivity (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', duration SMALLINT DEFAULT 0 NOT NULL, initial TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(1024) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SalesActivityReport (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', submitTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', content LONGTEXT DEFAULT NULL, SalesActivitySchedule_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_D62F90CCFFD4D80F (SalesActivitySchedule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SalesActivitySchedule (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', status VARCHAR(255) NOT NULL, startTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', endTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', CustomerAssignment_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', SalesActivity_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_5CF6B12DA1A6CD65 (CustomerAssignment_id), INDEX IDX_5CF6B12D7C14D328 (SalesActivity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SalesPerformanceMetric (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', lastModifiedTime DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', name VARCHAR(255) NOT NULL, metricType VARCHAR(255) NOT NULL, recurrenceType VARCHAR(255) NOT NULL, recurrenceCount SMALLINT DEFAULT NULL, displaySchema VARCHAR(1024) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SalesPerformanceMetricEvaluation (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', removed TINYINT(1) DEFAULT 0 NOT NULL, alias VARCHAR(255) NOT NULL, evaluationType VARCHAR(255) NOT NULL, SalesPerformanceMetric_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_7531B7DAA0219198 (SalesPerformanceMetric_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SalesRank (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', lastModifiedTime DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', name VARCHAR(255) NOT NULL, metricType VARCHAR(255) NOT NULL, evaluationType VARCHAR(255) NOT NULL, recurrenceType VARCHAR(255) NOT NULL, displaySalesNumber SMALLINT NOT NULL, queryOrder VARCHAR(255) NOT NULL, displaySchema VARCHAR(1024) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE VerificationReport (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', note LONGTEXT DEFAULT NULL, Customer_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CustomerVerification_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_8A06874E15094C24 (Customer_id), INDEX IDX_8A06874ED9179049 (CustomerVerification_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE City ADD CONSTRAINT FK_8D69AD0A6FDA9E9D FOREIGN KEY (Province_id) REFERENCES Province (id)');
        $this->addSql('ALTER TABLE ClosingRequest ADD CONSTRAINT FK_4F17BA90A1A6CD65 FOREIGN KEY (CustomerAssignment_id) REFERENCES CustomerAssignment (id)');
        $this->addSql('ALTER TABLE Customer ADD CONSTRAINT FK_784FEC5F44115B33 FOREIGN KEY (City_id) REFERENCES City (id)');
        $this->addSql('ALTER TABLE CustomerAssignment ADD CONSTRAINT FK_C4BFA65D5D244851 FOREIGN KEY (Sales_id) REFERENCES Sales (id)');
        $this->addSql('ALTER TABLE CustomerAssignment ADD CONSTRAINT FK_C4BFA65D15094C24 FOREIGN KEY (Customer_id) REFERENCES Customer (id)');
        $this->addSql('ALTER TABLE CustomerAssignment ADD CONSTRAINT FK_C4BFA65D21B36B88 FOREIGN KEY (CustomerJourney_id) REFERENCES CustomerJourney (id)');
        $this->addSql('ALTER TABLE RecycleRequest ADD CONSTRAINT FK_AF6BC755A1A6CD65 FOREIGN KEY (CustomerAssignment_id) REFERENCES CustomerAssignment (id)');
        $this->addSql('ALTER TABLE Sales ADD CONSTRAINT FK_AA405F40376337B3 FOREIGN KEY (Manager_id) REFERENCES Manager (id)');
        $this->addSql('ALTER TABLE Sales ADD CONSTRAINT FK_AA405F4044115B33 FOREIGN KEY (City_id) REFERENCES City (id)');
        $this->addSql('ALTER TABLE SalesActivityReport ADD CONSTRAINT FK_D62F90CCFFD4D80F FOREIGN KEY (SalesActivitySchedule_id) REFERENCES SalesActivitySchedule (id)');
        $this->addSql('ALTER TABLE SalesActivitySchedule ADD CONSTRAINT FK_5CF6B12DA1A6CD65 FOREIGN KEY (CustomerAssignment_id) REFERENCES CustomerAssignment (id)');
        $this->addSql('ALTER TABLE SalesActivitySchedule ADD CONSTRAINT FK_5CF6B12D7C14D328 FOREIGN KEY (SalesActivity_id) REFERENCES SalesActivity (id)');
        $this->addSql('ALTER TABLE SalesPerformanceMetricEvaluation ADD CONSTRAINT FK_7531B7DAA0219198 FOREIGN KEY (SalesPerformanceMetric_id) REFERENCES SalesPerformanceMetric (id)');
        $this->addSql('ALTER TABLE VerificationReport ADD CONSTRAINT FK_8A06874E15094C24 FOREIGN KEY (Customer_id) REFERENCES Customer (id)');
        $this->addSql('ALTER TABLE VerificationReport ADD CONSTRAINT FK_8A06874ED9179049 FOREIGN KEY (CustomerVerification_id) REFERENCES CustomerVerification (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE City DROP FOREIGN KEY FK_8D69AD0A6FDA9E9D');
        $this->addSql('ALTER TABLE ClosingRequest DROP FOREIGN KEY FK_4F17BA90A1A6CD65');
        $this->addSql('ALTER TABLE Customer DROP FOREIGN KEY FK_784FEC5F44115B33');
        $this->addSql('ALTER TABLE CustomerAssignment DROP FOREIGN KEY FK_C4BFA65D5D244851');
        $this->addSql('ALTER TABLE CustomerAssignment DROP FOREIGN KEY FK_C4BFA65D15094C24');
        $this->addSql('ALTER TABLE CustomerAssignment DROP FOREIGN KEY FK_C4BFA65D21B36B88');
        $this->addSql('ALTER TABLE RecycleRequest DROP FOREIGN KEY FK_AF6BC755A1A6CD65');
        $this->addSql('ALTER TABLE Sales DROP FOREIGN KEY FK_AA405F40376337B3');
        $this->addSql('ALTER TABLE Sales DROP FOREIGN KEY FK_AA405F4044115B33');
        $this->addSql('ALTER TABLE SalesActivityReport DROP FOREIGN KEY FK_D62F90CCFFD4D80F');
        $this->addSql('ALTER TABLE SalesActivitySchedule DROP FOREIGN KEY FK_5CF6B12DA1A6CD65');
        $this->addSql('ALTER TABLE SalesActivitySchedule DROP FOREIGN KEY FK_5CF6B12D7C14D328');
        $this->addSql('ALTER TABLE SalesPerformanceMetricEvaluation DROP FOREIGN KEY FK_7531B7DAA0219198');
        $this->addSql('ALTER TABLE VerificationReport DROP FOREIGN KEY FK_8A06874E15094C24');
        $this->addSql('ALTER TABLE VerificationReport DROP FOREIGN KEY FK_8A06874ED9179049');
        $this->addSql('DROP TABLE `Admin`');
        $this->addSql('DROP TABLE City');
        $this->addSql('DROP TABLE ClosingRequest');
        $this->addSql('DROP TABLE CommonSalesMetric');
        $this->addSql('DROP TABLE CompanyMetric');
        $this->addSql('DROP TABLE Customer');
        $this->addSql('DROP TABLE CustomerAssignment');
        $this->addSql('DROP TABLE CustomerJourney');
        $this->addSql('DROP TABLE CustomerVerification');
        $this->addSql('DROP TABLE Manager');
        $this->addSql('DROP TABLE Province');
        $this->addSql('DROP TABLE RecycleRequest');
        $this->addSql('DROP TABLE Sales');
        $this->addSql('DROP TABLE SalesActivity');
        $this->addSql('DROP TABLE SalesActivityReport');
        $this->addSql('DROP TABLE SalesActivitySchedule');
        $this->addSql('DROP TABLE SalesPerformanceMetric');
        $this->addSql('DROP TABLE SalesPerformanceMetricEvaluation');
        $this->addSql('DROP TABLE SalesRank');
        $this->addSql('DROP TABLE VerificationReport');
    }
}
