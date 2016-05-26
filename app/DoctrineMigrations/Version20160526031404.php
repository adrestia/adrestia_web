<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160526031404 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE report_reasons (id INT AUTO_INCREMENT NOT NULL, reason VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }


    public function postUp(Schema $schema) {
        $this->connection->executeQuery("INSERT INTO report_reasons (`reason`) VALUES ('Spam');");
        $this->connection->executeQuery("INSERT INTO report_reasons (`reason`) VALUES ('Harrassment');");
        $this->connection->executeQuery("INSERT INTO report_reasons (`reason`) VALUES ('Doxxing');");
        $this->connection->executeQuery("INSERT INTO report_reasons (`reason`) VALUES ('Threats');");
    }
        
    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE report_reasons');
    }
}
