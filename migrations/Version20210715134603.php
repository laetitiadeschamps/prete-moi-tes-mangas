<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210715134603 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_manga (category_id INT NOT NULL, manga_id INT NOT NULL, INDEX IDX_9E69D14A12469DE2 (category_id), INDEX IDX_9E69D14A7B6461 (manga_id), PRIMARY KEY(category_id, manga_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE manga (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, author VARCHAR(100) NOT NULL, picture VARCHAR(100) DEFAULT NULL, synopsis LONGTEXT DEFAULT NULL, volume_number INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, chat_id INT NOT NULL, content LONGTEXT NOT NULL, object VARCHAR(100) DEFAULT NULL, status SMALLINT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_B6BD307FF675F31B (author_id), INDEX IDX_B6BD307F1A9A7125 (chat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, pseudo VARCHAR(100) NOT NULL, lastname VARCHAR(100) NOT NULL, firstname VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, picture VARCHAR(255) DEFAULT NULL, address VARCHAR(255) NOT NULL, zip_code SMALLINT UNSIGNED NOT NULL, city VARCHAR(255) NOT NULL, holiday_mode TINYINT(1) NOT NULL, status SMALLINT NOT NULL, latitude INT NOT NULL, longitude INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D64986CC499D (pseudo), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_volume (user_id INT NOT NULL, volume_id INT NOT NULL, INDEX IDX_DCC8D6E3A76ED395 (user_id), INDEX IDX_DCC8D6E38FD80EEA (volume_id), PRIMARY KEY(user_id, volume_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_chat (user_id INT NOT NULL, chat_id INT NOT NULL, INDEX IDX_1F1CBE63A76ED395 (user_id), INDEX IDX_1F1CBE631A9A7125 (chat_id), PRIMARY KEY(user_id, chat_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE volume (id INT AUTO_INCREMENT NOT NULL, manga_id INT NOT NULL, number INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_B99ACDDE7B6461 (manga_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category_manga ADD CONSTRAINT FK_9E69D14A12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_manga ADD CONSTRAINT FK_9E69D14A7B6461 FOREIGN KEY (manga_id) REFERENCES manga (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F1A9A7125 FOREIGN KEY (chat_id) REFERENCES chat (id)');
        $this->addSql('ALTER TABLE user_volume ADD CONSTRAINT FK_DCC8D6E3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_volume ADD CONSTRAINT FK_DCC8D6E38FD80EEA FOREIGN KEY (volume_id) REFERENCES volume (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_chat ADD CONSTRAINT FK_1F1CBE63A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_chat ADD CONSTRAINT FK_1F1CBE631A9A7125 FOREIGN KEY (chat_id) REFERENCES chat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE volume ADD CONSTRAINT FK_B99ACDDE7B6461 FOREIGN KEY (manga_id) REFERENCES manga (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_manga DROP FOREIGN KEY FK_9E69D14A12469DE2');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F1A9A7125');
        $this->addSql('ALTER TABLE user_chat DROP FOREIGN KEY FK_1F1CBE631A9A7125');
        $this->addSql('ALTER TABLE category_manga DROP FOREIGN KEY FK_9E69D14A7B6461');
        $this->addSql('ALTER TABLE volume DROP FOREIGN KEY FK_B99ACDDE7B6461');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF675F31B');
        $this->addSql('ALTER TABLE user_volume DROP FOREIGN KEY FK_DCC8D6E3A76ED395');
        $this->addSql('ALTER TABLE user_chat DROP FOREIGN KEY FK_1F1CBE63A76ED395');
        $this->addSql('ALTER TABLE user_volume DROP FOREIGN KEY FK_DCC8D6E38FD80EEA');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE category_manga');
        $this->addSql('DROP TABLE chat');
        $this->addSql('DROP TABLE manga');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_volume');
        $this->addSql('DROP TABLE user_chat');
        $this->addSql('DROP TABLE volume');
    }
}
