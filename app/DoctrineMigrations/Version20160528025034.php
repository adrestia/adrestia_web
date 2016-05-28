<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160528025034 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reports DROP INDEX UNIQ_F11FA7454B89032C, ADD INDEX IDX_F11FA7454B89032C (post_id)');
        $this->addSql('ALTER TABLE reports ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA745A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F11FA745A76ED395 ON reports (user_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reports DROP INDEX IDX_F11FA7454B89032C, ADD UNIQUE INDEX UNIQ_F11FA7454B89032C (post_id)');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745A76ED395');
        $this->addSql('DROP INDEX UNIQ_F11FA745A76ED395 ON reports');
        $this->addSql('ALTER TABLE reports DROP user_id');
    }
}
