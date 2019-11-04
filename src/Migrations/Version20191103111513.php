<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191103111513 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE bundesliga_match CHANGE is_win_by_default win_by_default TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE bundesliga_relegation_match CHANGE is_win_by_default win_by_default TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE bundesliga_match CHANGE win_by_default is_win_by_default TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE bundesliga_relegation_match CHANGE win_by_default is_win_by_default TINYINT(1) NOT NULL');
    }
}
