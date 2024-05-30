<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240530074733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE CompanyMetric (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', lastModifiedTime DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', name VARCHAR(255) NOT NULL, target INT DEFAULT NULL, metricType VARCHAR(255) NOT NULL, evaluationType VARCHAR(255) NOT NULL, recurrenceType VARCHAR(255) NOT NULL, recurrenceCount SMALLINT DEFAULT NULL, displaySchema VARCHAR(1024) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SalesPerformanceMetric (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', lastModifiedTime DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', name VARCHAR(255) NOT NULL, metricType VARCHAR(255) NOT NULL, recurrenceType VARCHAR(255) NOT NULL, recurrenceCount SMALLINT DEFAULT NULL, displaySchema VARCHAR(1024) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SalesPerformanceMetricEvaluation (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', removed TINYINT(1) DEFAULT 0 NOT NULL, alias VARCHAR(255) NOT NULL, evaluationType VARCHAR(255) NOT NULL, SalesPerformanceMetric_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_7531B7DAA0219198 (SalesPerformanceMetric_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SalesRank (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', lastModifiedTime DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', name VARCHAR(255) NOT NULL, metricType VARCHAR(255) NOT NULL, evaluationType VARCHAR(255) NOT NULL, recurrenceType VARCHAR(255) NOT NULL, displaySalesNumber SMALLINT DEFAULT NULL, `order` VARCHAR(255) NOT NULL, displaySchema VARCHAR(1024) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE SalesPerformanceMetricEvaluation ADD CONSTRAINT FK_7531B7DAA0219198 FOREIGN KEY (SalesPerformanceMetric_id) REFERENCES SalesPerformanceMetric (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE SalesPerformanceMetricEvaluation DROP FOREIGN KEY FK_7531B7DAA0219198');
        $this->addSql('DROP TABLE CompanyMetric');
        $this->addSql('DROP TABLE SalesPerformanceMetric');
        $this->addSql('DROP TABLE SalesPerformanceMetricEvaluation');
        $this->addSql('DROP TABLE SalesRank');
    }
}
