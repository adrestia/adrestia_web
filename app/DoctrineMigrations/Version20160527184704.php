<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160527184704 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE announcements CHANGE body body VARCHAR(4096) NOT NULL');
        $this->addSql('ALTER TABLE users_roles RENAME INDEX idx_2de8c6a3a76ed395 TO IDX_51498A8EA76ED395');
        $this->addSql('ALTER TABLE users_roles RENAME INDEX idx_2de8c6a3d60322ac TO IDX_51498A8ED60322AC');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE announcements CHANGE body body VARCHAR(1023) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE users_roles RENAME INDEX idx_51498a8ea76ed395 TO IDX_2DE8C6A3A76ED395');
        $this->addSql('ALTER TABLE users_roles RENAME INDEX idx_51498a8ed60322ac TO IDX_2DE8C6A3D60322AC');
    }
}
