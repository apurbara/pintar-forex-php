<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231021140302 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `Admin` (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', aSuperUser TINYINT(1) DEFAULT 0 NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(60) DEFAULT NULL, resetPasswordToken VARCHAR(64) DEFAULT NULL, resetPasswordTokenExpiredTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', UNIQUE INDEX admin_mail_idx (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Personnel (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(60) DEFAULT NULL, resetPasswordToken VARCHAR(64) DEFAULT NULL, resetPasswordTokenExpiredTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', UNIQUE INDEX personnel_mail_idx (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE `Admin`');
        $this->addSql('DROP TABLE Personnel');
    }
}
