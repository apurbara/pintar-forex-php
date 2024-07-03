<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240629135435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Sales DROP FOREIGN KEY FK_AA405F4072B27900');
        $this->addSql('DROP INDEX IDX_AA405F4072B27900 ON Sales');
        $this->addSql('ALTER TABLE Sales CHANGE Area_id City_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE Sales ADD CONSTRAINT FK_AA405F4044115B33 FOREIGN KEY (City_id) REFERENCES City (id)');
        $this->addSql('CREATE INDEX IDX_AA405F4044115B33 ON Sales (City_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Sales DROP FOREIGN KEY FK_AA405F4044115B33');
        $this->addSql('DROP INDEX IDX_AA405F4044115B33 ON Sales');
        $this->addSql('ALTER TABLE Sales CHANGE City_id Area_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE Sales ADD CONSTRAINT FK_AA405F4072B27900 FOREIGN KEY (Area_id) REFERENCES City (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_AA405F4072B27900 ON Sales (Area_id)');
    }
}
