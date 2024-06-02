<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240602160234 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Manager DROP FOREIGN KEY FK_35991C254214B8D');
        $this->addSql('ALTER TABLE Sales DROP FOREIGN KEY FK_AA405F404214B8D');
        $this->addSql('DROP TABLE Personnel');
        $this->addSql('DROP INDEX IDX_35991C254214B8D ON Manager');
        $this->addSql('ALTER TABLE Manager ADD name VARCHAR(255) NOT NULL, ADD email VARCHAR(255) NOT NULL, ADD password VARCHAR(60) DEFAULT NULL, ADD resetPasswordToken VARCHAR(64) DEFAULT NULL, ADD resetPasswordTokenExpiredTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', DROP Personnel_id, CHANGE disabled suspended TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX manager_mail_idx ON Manager (email)');
        $this->addSql('DROP INDEX IDX_AA405F404214B8D ON Sales');
        $this->addSql('ALTER TABLE Sales ADD name VARCHAR(255) NOT NULL, ADD email VARCHAR(255) NOT NULL, ADD password VARCHAR(60) DEFAULT NULL, ADD resetPasswordToken VARCHAR(64) DEFAULT NULL, ADD resetPasswordTokenExpiredTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', DROP Personnel_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Personnel (id CHAR(36) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'(DC2Type:guid)\', suspended TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', name VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, password VARCHAR(60) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, resetPasswordToken VARCHAR(64) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, resetPasswordTokenExpiredTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', UNIQUE INDEX personnel_mail_idx (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP INDEX manager_mail_idx ON Manager');
        $this->addSql('ALTER TABLE Manager ADD Personnel_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', DROP name, DROP email, DROP password, DROP resetPasswordToken, DROP resetPasswordTokenExpiredTime, CHANGE suspended disabled TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE Manager ADD CONSTRAINT FK_35991C254214B8D FOREIGN KEY (Personnel_id) REFERENCES Personnel (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_35991C254214B8D ON Manager (Personnel_id)');
        $this->addSql('ALTER TABLE Sales ADD Personnel_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', DROP name, DROP email, DROP password, DROP resetPasswordToken, DROP resetPasswordTokenExpiredTime');
        $this->addSql('ALTER TABLE Sales ADD CONSTRAINT FK_AA405F404214B8D FOREIGN KEY (Personnel_id) REFERENCES Personnel (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_AA405F404214B8D ON Sales (Personnel_id)');
    }
}
