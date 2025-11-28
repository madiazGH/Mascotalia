<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251126230712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mascota (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, especie VARCHAR(255) NOT NULL, edad INT NOT NULL, tamaño VARCHAR(255) NOT NULL, descripcion VARCHAR(255) NOT NULL, imagen VARCHAR(255) NOT NULL, disponible TINYINT(1) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE solicitud (id INT AUTO_INCREMENT NOT NULL, fecha_envio DATE NOT NULL, estado VARCHAR(255) NOT NULL, usuario_id INT NOT NULL, mascota_id INT NOT NULL, INDEX IDX_96D27CC0DB38439E (usuario_id), INDEX IDX_96D27CC0FB60C59E (mascota_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE usuario (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) DEFAULT NULL, apellido VARCHAR(255) DEFAULT NULL, dni VARCHAR(8) DEFAULT NULL, fecha_nacimiento DATE DEFAULT NULL, provincia VARCHAR(255) DEFAULT NULL, ciudad VARCHAR(255) DEFAULT NULL, direccion VARCHAR(255) DEFAULT NULL, telefono VARCHAR(20) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, contraseña VARCHAR(255) NOT NULL, rol LONGTEXT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE solicitud ADD CONSTRAINT FK_96D27CC0DB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE solicitud ADD CONSTRAINT FK_96D27CC0FB60C59E FOREIGN KEY (mascota_id) REFERENCES mascota (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE solicitud DROP FOREIGN KEY FK_96D27CC0DB38439E');
        $this->addSql('ALTER TABLE solicitud DROP FOREIGN KEY FK_96D27CC0FB60C59E');
        $this->addSql('DROP TABLE mascota');
        $this->addSql('DROP TABLE solicitud');
        $this->addSql('DROP TABLE usuario');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
