<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251129221255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mascota ADD tamano VARCHAR(255) NOT NULL, CHANGE edad edad VARCHAR(20) NOT NULL, CHANGE disponible disponible TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE usuario CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE rol rol LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mascota DROP tamano, CHANGE edad edad VARCHAR(20) DEFAULT \'\' NOT NULL, CHANGE disponible disponible VARCHAR(50) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE usuario CHANGE email email VARCHAR(255) NOT NULL, CHANGE rol rol VARCHAR(50) DEFAULT \'\' NOT NULL');
    }
}
