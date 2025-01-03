<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241123190050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Vérifie si la colonne parent_id existe déjà dans la table categories
        $table = $schema->getTable('categories');
        if (!$table->hasColumn('parent_id')) {
            $this->addSql('ALTER TABLE categories ADD parent_id INT DEFAULT NULL');
        }

        // Vérifie si la contrainte FK_3AF34668727ACA70 existe déjà
        if (!$table->hasForeignKey('FK_3AF34668727ACA70')) {
            $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF34668727ACA70 FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE CASCADE');
        }

        // Vérifie si l'index IDX_3AF34668727ACA70 existe déjà
        if (!$table->hasIndex('IDX_3AF34668727ACA70')) {
            $this->addSql('CREATE INDEX IDX_3AF34668727ACA70 ON categories (parent_id)');
        }

        // Applique les modifications de la table products (ajout des colonnes et modification de category_id)
        $this->addSql('ALTER TABLE products ADD price INT NOT NULL, ADD stock INT NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD slug VARCHAR(255) NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE category_id categories_id INT NOT NULL');

        // Vérifie si la contrainte FK_B3BA5A5AA21214B7 existe déjà pour la table products
        $table = $schema->getTable('products');
        if (!$table->hasForeignKey('FK_B3BA5A5AA21214B7')) {
            $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5AA21214B7 FOREIGN KEY (categories_id) REFERENCES categories (id)');
        }

        // Vérifie si l'index IDX_B3BA5A5AA21214B7 existe déjà pour la table products
        if (!$table->hasIndex('IDX_B3BA5A5AA21214B7')) {
            $this->addSql('CREATE INDEX IDX_B3BA5A5AA21214B7 ON products (categories_id)');
        }
    }

    public function down(Schema $schema): void
    {
        // Supprime la contrainte et l'index de la table categories
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668727ACA70');
        $this->addSql('DROP INDEX IDX_3AF34668727ACA70 ON categories');

        // Supprime la contrainte et l'index de la table products
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5AA21214B7');
        $this->addSql('DROP INDEX IDX_B3BA5A5AA21214B7 ON products');

        // Restaure les colonnes dans la table products
        $this->addSql('ALTER TABLE products ADD category_id INT NOT NULL, DROP categories_id, DROP price, DROP stock, DROP created_at, DROP slug, CHANGE description description LONGTEXT DEFAULT NULL');
    }
}
