<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180618211812 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_visit ADD user_id INT DEFAULT NULL, DROP user');
        $this->addSql('ALTER TABLE user_visit ADD CONSTRAINT FK_A1BC1261A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_A1BC1261A76ED395 ON user_visit (user_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_visit DROP FOREIGN KEY FK_A1BC1261A76ED395');
        $this->addSql('DROP INDEX IDX_A1BC1261A76ED395 ON user_visit');
        $this->addSql('ALTER TABLE user_visit ADD user VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, DROP user_id');
    }
}
