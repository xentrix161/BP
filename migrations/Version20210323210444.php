<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210323210444 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Shop (id INT AUTO_INCREMENT NOT NULL, profit DOUBLE PRECISION NOT NULL, order_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order` ADD mobile VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE article ADD user_id INT NOT NULL, ADD rating DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD activate TINYINT(1) DEFAULT NULL, ADD token VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE Shop');
        $this->addSql('ALTER TABLE article DROP user_id, DROP rating');
        $this->addSql('ALTER TABLE `Order` DROP mobile');
        $this->addSql('ALTER TABLE user DROP activate, DROP token');
    }
}
