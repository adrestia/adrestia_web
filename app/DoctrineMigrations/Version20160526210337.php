<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160526210337 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE roles (id INT AUTO_INCREMENT NOT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE users ADD roles_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E938C751C4 FOREIGN KEY (roles_id) REFERENCES roles (id)');
        $this->addSql('CREATE INDEX IDX_1483A5E938C751C4 ON users (roles_id)');
    }
    
    /**
     * @param Schema $schema
     */
    public function postUp(Schema $schema) {
        $this->connection->executeQuery("INSERT INTO roles (`role`) VALUES ('ROLE_USER');");
        $this->connection->executeQuery("INSERT INTO roles (`role`) VALUES ('ROLE_ADMIN');");
        $this->connection->executeQuery("INSERT INTO roles (`role`) VALUES ('ROLE_SUPER_ADMIN');");
        
        $em = $this->container->get('doctrine.orm.entity_manager');
        $users = $em->getRepository('AppBundle:User')
                    ->fetchAll();
        
        $batch_size = 10;
        $i = 0;
        
        for($users as $user) {
            $user->addRole("ROLE_USER");
            $em->persist($user);
            if(($i % $batch_size) === 0) {
                $em->flush();
                $em->clear();
            }
            ++$i;
            $em->flush();
            $em->clear();
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E938C751C4');
        $this->addSql('DROP TABLE roles');
        $this->addSql('DROP INDEX IDX_1483A5E938C751C4 ON users');
        $this->addSql('ALTER TABLE users DROP roles_id');
    }
}
