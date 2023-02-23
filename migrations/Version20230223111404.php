<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230223111404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, cql_search LONGTEXT NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE material (id INT AUTO_INCREMENT NOT NULL, title_full VARCHAR(255) NOT NULL, creator_filtered VARCHAR(255) DEFAULT NULL, creator VARCHAR(255) DEFAULT NULL, creator_aut VARCHAR(255) DEFAULT NULL, creator_cre VARCHAR(255) DEFAULT NULL, contributor VARCHAR(255) DEFAULT NULL, contributor_act VARCHAR(255) DEFAULT NULL, contributor_aut VARCHAR(255) DEFAULT NULL, contributor_ctb VARCHAR(255) DEFAULT NULL, contributor_dkfig VARCHAR(255) DEFAULT NULL, abstract LONGTEXT DEFAULT NULL, pid VARCHAR(25) NOT NULL, publisher VARCHAR(255) NOT NULL, date DATE NOT NULL, uri VARCHAR(255) NOT NULL, cover_url VARCHAR(255) NOT NULL, type VARCHAR(255) DEFAULT NULL, INDEX title_idx (title_full), INDEX creator_idx (creator), UNIQUE INDEX pid_unique (pid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE material_category (material_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_40943D63E308AC6F (material_id), INDEX IDX_40943D6312469DE2 (category_id), PRIMARY KEY(material_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE search_run (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, is_success TINYINT(1) NOT NULL, error_message LONGTEXT DEFAULT NULL, run_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_94CDD07F12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE material_category ADD CONSTRAINT FK_40943D63E308AC6F FOREIGN KEY (material_id) REFERENCES material (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE material_category ADD CONSTRAINT FK_40943D6312469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE search_run ADD CONSTRAINT FK_94CDD07F12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE material_category DROP FOREIGN KEY FK_40943D63E308AC6F');
        $this->addSql('ALTER TABLE material_category DROP FOREIGN KEY FK_40943D6312469DE2');
        $this->addSql('ALTER TABLE search_run DROP FOREIGN KEY FK_94CDD07F12469DE2');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE material');
        $this->addSql('DROP TABLE material_category');
        $this->addSql('DROP TABLE search_run');
    }
}
