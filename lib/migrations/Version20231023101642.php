<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231023101642 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Area (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', name VARCHAR(255) NOT NULL, description VARCHAR(1024) DEFAULT NULL, AreaStructure_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', Area_idOfParent CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_77A692565FBCB880 (AreaStructure_id), INDEX IDX_77A692565BF92604 (Area_idOfParent), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE AreaStructure (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', disabled TINYINT(1) DEFAULT 0 NOT NULL, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', name VARCHAR(255) NOT NULL, description VARCHAR(1024) DEFAULT NULL, AreaStructure_idOfParent CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_B341E51461F3BD24 (AreaStructure_idOfParent), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Area ADD CONSTRAINT FK_77A692565FBCB880 FOREIGN KEY (AreaStructure_id) REFERENCES AreaStructure (id)');
        $this->addSql('ALTER TABLE Area ADD CONSTRAINT FK_77A692565BF92604 FOREIGN KEY (Area_idOfParent) REFERENCES Area (id)');
        $this->addSql('ALTER TABLE AreaStructure ADD CONSTRAINT FK_B341E51461F3BD24 FOREIGN KEY (AreaStructure_idOfParent) REFERENCES AreaStructure (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Area DROP FOREIGN KEY FK_77A692565FBCB880');
        $this->addSql('ALTER TABLE Area DROP FOREIGN KEY FK_77A692565BF92604');
        $this->addSql('ALTER TABLE AreaStructure DROP FOREIGN KEY FK_B341E51461F3BD24');
        $this->addSql('DROP TABLE Area');
        $this->addSql('DROP TABLE AreaStructure');
    }
}
