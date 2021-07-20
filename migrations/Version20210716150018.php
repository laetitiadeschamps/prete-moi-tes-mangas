<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210716150018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_volume DROP FOREIGN KEY FK_DCC8D6E38FD80EEA');
        $this->addSql('ALTER TABLE user_volume DROP FOREIGN KEY FK_DCC8D6E3A76ED395');
        $this->addSql('ALTER TABLE user_volume ADD id INT AUTO_INCREMENT NOT NULL, ADD status TINYINT(1) NOT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE user_volume ADD CONSTRAINT FK_DCC8D6E38FD80EEA FOREIGN KEY (volume_id) REFERENCES volume (id)');
        $this->addSql('ALTER TABLE user_volume ADD CONSTRAINT FK_DCC8D6E3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_volume MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE user_volume DROP FOREIGN KEY FK_DCC8D6E3A76ED395');
        $this->addSql('ALTER TABLE user_volume DROP FOREIGN KEY FK_DCC8D6E38FD80EEA');
        $this->addSql('ALTER TABLE user_volume DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE user_volume DROP id, DROP status, DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE user_volume ADD CONSTRAINT FK_DCC8D6E3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_volume ADD CONSTRAINT FK_DCC8D6E38FD80EEA FOREIGN KEY (volume_id) REFERENCES volume (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_volume ADD PRIMARY KEY (user_id, volume_id)');
    }
}
