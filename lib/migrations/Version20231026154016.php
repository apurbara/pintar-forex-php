<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231026154016 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE SalesActivityReport (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', submitTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', content LONGTEXT DEFAULT NULL, SalesActivitySchedule_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_D62F90CCFFD4D80F (SalesActivitySchedule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE SalesActivityReport ADD CONSTRAINT FK_D62F90CCFFD4D80F FOREIGN KEY (SalesActivitySchedule_id) REFERENCES SalesActivitySchedule (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE SalesActivityReport DROP FOREIGN KEY FK_D62F90CCFFD4D80F');
        $this->addSql('DROP TABLE SalesActivityReport');
    }
}
