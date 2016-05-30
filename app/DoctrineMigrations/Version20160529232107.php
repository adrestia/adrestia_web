<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160529232107 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO report_reasons (`reason`) VALUES ('This post is harrassing or targeting a specifically named individual (first and last name)');");
        $this->addSql("INSERT INTO report_reasons (`reason`) VALUES ('This post is attempting to give out personal information about a poster or student');");        
        $this->addSql("INSERT INTO report_reasons (`reason`) VALUES ('This post is threating harm to the poster, another student, or the student body');");
        $this->addSql("INSERT INTO report_reasons (`reason`) VALUES ('This post is spam (advertising, soliciting, pornography, etc.)');");
        $this->addSql("INSERT INTO report_reasons (`reason`) VALUES ('This post links to pornographic or illegal material');");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM report_reasons WHERE reason='This post is harrassing or targeting a specifically named individual (first and last name)';");
        $this->addSql("DELETE FROM report_reasons WHERE reason='This post is attempting to give out personal information about a poster or student';");
        $this->addSql("DELETE FROM report_reasons WHERE reason='This post is threating harm to the poster, another student, or the student body';");
        $this->addSql("DELETE FROM report_reasons WHERE reason='This post is spam (advertising, soliciting, pornography, etc.)';");
        $this->addSql("DELETE FROM report_reasons WHERE reason='This post links to pornographic or illegal material';");
    }
}
