<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240630020116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Sales ADD Manager_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE Sales ADD CONSTRAINT FK_AA405F40376337B3 FOREIGN KEY (Manager_id) REFERENCES Manager (id)');
        $this->addSql('CREATE INDEX IDX_AA405F40376337B3 ON Sales (Manager_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Sales DROP FOREIGN KEY FK_AA405F40376337B3');
        $this->addSql('DROP INDEX IDX_AA405F40376337B3 ON Sales');
        $this->addSql('ALTER TABLE Sales DROP Manager_id');
    }
}
