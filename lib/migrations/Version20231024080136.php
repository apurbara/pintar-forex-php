<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231024080136 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Manager (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, Personnel_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_35991C254214B8D (Personnel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Sales (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, type VARCHAR(255) NOT NULL, Manager_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', Personnel_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', Area_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_AA405F40376337B3 (Manager_id), INDEX IDX_AA405F404214B8D (Personnel_id), INDEX IDX_AA405F4072B27900 (Area_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Manager ADD CONSTRAINT FK_35991C254214B8D FOREIGN KEY (Personnel_id) REFERENCES Personnel (id)');
        $this->addSql('ALTER TABLE Sales ADD CONSTRAINT FK_AA405F40376337B3 FOREIGN KEY (Manager_id) REFERENCES Manager (id)');
        $this->addSql('ALTER TABLE Sales ADD CONSTRAINT FK_AA405F404214B8D FOREIGN KEY (Personnel_id) REFERENCES Personnel (id)');
        $this->addSql('ALTER TABLE Sales ADD CONSTRAINT FK_AA405F4072B27900 FOREIGN KEY (Area_id) REFERENCES Area (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Manager DROP FOREIGN KEY FK_35991C254214B8D');
        $this->addSql('ALTER TABLE Sales DROP FOREIGN KEY FK_AA405F40376337B3');
        $this->addSql('ALTER TABLE Sales DROP FOREIGN KEY FK_AA405F404214B8D');
        $this->addSql('ALTER TABLE Sales DROP FOREIGN KEY FK_AA405F4072B27900');
        $this->addSql('DROP TABLE Manager');
        $this->addSql('DROP TABLE Sales');
    }
}
