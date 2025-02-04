<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250203200139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE groups (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F06D39705E237E06 ON groups (name)');
        $this->addSql('CREATE TABLE user_groups (user_id INT NOT NULL, group_id INT NOT NULL, PRIMARY KEY(user_id, group_id))');
        $this->addSql('CREATE INDEX IDX_953F224DA76ED395 ON user_groups (user_id)');
        $this->addSql('CREATE INDEX IDX_953F224DFE54D947 ON user_groups (group_id)');
        $this->addSql('ALTER TABLE user_groups ADD CONSTRAINT FK_953F224DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_groups ADD CONSTRAINT FK_953F224DFE54D947 FOREIGN KEY (group_id) REFERENCES groups (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users DROP name');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE user_groups DROP CONSTRAINT FK_953F224DA76ED395');
        $this->addSql('ALTER TABLE user_groups DROP CONSTRAINT FK_953F224DFE54D947');
        $this->addSql('DROP TABLE groups');
        $this->addSql('DROP TABLE user_groups');
        $this->addSql('ALTER TABLE users ADD name VARCHAR(50) NOT NULL');
    }
}
