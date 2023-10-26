<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231026145146 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE SalesActivitySchedule (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', status VARCHAR(255) NOT NULL, startTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', endTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', AssignedCustomer_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', SalesActivity_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_5CF6B12D6867A939 (AssignedCustomer_id), INDEX IDX_5CF6B12D7C14D328 (SalesActivity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE SalesActivitySchedule ADD CONSTRAINT FK_5CF6B12D6867A939 FOREIGN KEY (AssignedCustomer_id) REFERENCES AssignedCustomer (id)');
        $this->addSql('ALTER TABLE SalesActivitySchedule ADD CONSTRAINT FK_5CF6B12D7C14D328 FOREIGN KEY (SalesActivity_id) REFERENCES SalesActivity (id)');
        $this->addSql('ALTER TABLE ScheduledSalesActivity DROP FOREIGN KEY FK_986DCD1F6867A939');
        $this->addSql('ALTER TABLE ScheduledSalesActivity DROP FOREIGN KEY FK_986DCD1F7C14D328');
        $this->addSql('DROP TABLE ScheduledSalesActivity');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ScheduledSalesActivity (id CHAR(36) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', status VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, startTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', endTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', AssignedCustomer_id CHAR(36) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'(DC2Type:guid)\', SalesActivity_id CHAR(36) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'(DC2Type:guid)\', INDEX IDX_986DCD1F7C14D328 (SalesActivity_id), INDEX IDX_986DCD1F6867A939 (AssignedCustomer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE ScheduledSalesActivity ADD CONSTRAINT FK_986DCD1F6867A939 FOREIGN KEY (AssignedCustomer_id) REFERENCES AssignedCustomer (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE ScheduledSalesActivity ADD CONSTRAINT FK_986DCD1F7C14D328 FOREIGN KEY (SalesActivity_id) REFERENCES SalesActivity (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE SalesActivitySchedule DROP FOREIGN KEY FK_5CF6B12D6867A939');
        $this->addSql('ALTER TABLE SalesActivitySchedule DROP FOREIGN KEY FK_5CF6B12D7C14D328');
        $this->addSql('DROP TABLE SalesActivitySchedule');
    }
}
