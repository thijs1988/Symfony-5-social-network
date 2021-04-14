<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210212164701 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE counter ADD post_id INT DEFAULT NULL, DROP post');
        $this->addSql('ALTER TABLE counter ADD CONSTRAINT FK_C12294784B89032C FOREIGN KEY (post_id) REFERENCES micro_post (id)');
        $this->addSql('CREATE INDEX IDX_C12294784B89032C ON counter (post_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE counter DROP FOREIGN KEY FK_C12294784B89032C');
        $this->addSql('DROP INDEX IDX_C12294784B89032C ON counter');
        $this->addSql('ALTER TABLE counter ADD post INT NOT NULL, DROP post_id');
    }
}
