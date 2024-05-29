<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240521044523 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ClosingRequest DROP FOREIGN KEY FK_4F17BA906867A939');
        $this->addSql('ALTER TABLE RecycleRequest DROP FOREIGN KEY FK_AF6BC7556867A939');
        $this->addSql('ALTER TABLE SalesActivitySchedule DROP FOREIGN KEY FK_5CF6B12D6867A939');
        $this->addSql('CREATE TABLE CustomerAssignment (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', status VARCHAR(255) NOT NULL, Sales_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', Customer_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CustomerJourney_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_C4BFA65D5D244851 (Sales_id), INDEX IDX_C4BFA65D15094C24 (Customer_id), INDEX IDX_C4BFA65D21B36B88 (CustomerJourney_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE CustomerAssignment ADD CONSTRAINT FK_C4BFA65D5D244851 FOREIGN KEY (Sales_id) REFERENCES Sales (id)');
        $this->addSql('ALTER TABLE CustomerAssignment ADD CONSTRAINT FK_C4BFA65D15094C24 FOREIGN KEY (Customer_id) REFERENCES Customer (id)');
        $this->addSql('ALTER TABLE CustomerAssignment ADD CONSTRAINT FK_C4BFA65D21B36B88 FOREIGN KEY (CustomerJourney_id) REFERENCES CustomerJourney (id)');
        $this->addSql('ALTER TABLE AssignedCustomer DROP FOREIGN KEY FK_11A0CED015094C24');
        $this->addSql('ALTER TABLE AssignedCustomer DROP FOREIGN KEY FK_11A0CED05D244851');
        $this->addSql('ALTER TABLE AssignedCustomer DROP FOREIGN KEY FK_11A0CED021B36B88');
        $this->addSql('DROP TABLE AssignedCustomer');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE AssignedCustomer (id CHAR(36) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'(DC2Type:guid)\', status VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, createdTime DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', Sales_id CHAR(36) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'(DC2Type:guid)\', Customer_id CHAR(36) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'(DC2Type:guid)\', CustomerJourney_id CHAR(36) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci` COMMENT \'(DC2Type:guid)\', INDEX IDX_11A0CED05D244851 (Sales_id), INDEX IDX_11A0CED015094C24 (Customer_id), INDEX IDX_11A0CED021B36B88 (CustomerJourney_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE AssignedCustomer ADD CONSTRAINT FK_11A0CED015094C24 FOREIGN KEY (Customer_id) REFERENCES Customer (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE AssignedCustomer ADD CONSTRAINT FK_11A0CED05D244851 FOREIGN KEY (Sales_id) REFERENCES Sales (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE AssignedCustomer ADD CONSTRAINT FK_11A0CED021B36B88 FOREIGN KEY (CustomerJourney_id) REFERENCES CustomerJourney (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE CustomerAssignment DROP FOREIGN KEY FK_C4BFA65D5D244851');
        $this->addSql('ALTER TABLE CustomerAssignment DROP FOREIGN KEY FK_C4BFA65D15094C24');
        $this->addSql('ALTER TABLE CustomerAssignment DROP FOREIGN KEY FK_C4BFA65D21B36B88');
        $this->addSql('DROP TABLE CustomerAssignment');
    }
}
