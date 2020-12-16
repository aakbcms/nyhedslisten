<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201216121003 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE material_category DROP FOREIGN KEY FK_25C2509C650760A9');
        $this->addSql('ALTER TABLE material_category DROP FOREIGN KEY FK_25C2509CE308AC6F');
        $this->addSql('DROP INDEX idx_25c2509ce308ac6f ON material_category');
        $this->addSql('CREATE INDEX IDX_40943D63E308AC6F ON material_category (material_id)');
        $this->addSql('DROP INDEX idx_25c2509c650760a9 ON material_category');
        $this->addSql('CREATE INDEX IDX_40943D6312469DE2 ON material_category (category_id)');
        $this->addSql('ALTER TABLE material_category ADD CONSTRAINT FK_25C2509C650760A9 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE material_category ADD CONSTRAINT FK_25C2509CE308AC6F FOREIGN KEY (material_id) REFERENCES material (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE search_run DROP FOREIGN KEY FK_94CDD07F650760A9');
        $this->addSql('DROP INDEX IDX_94CDD07F650760A9 ON search_run');
        $this->addSql('ALTER TABLE search_run CHANGE search_id category_id INT NOT NULL');
        $this->addSql('ALTER TABLE search_run ADD CONSTRAINT FK_94CDD07F12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('CREATE INDEX IDX_94CDD07F12469DE2 ON search_run (category_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE material_category DROP FOREIGN KEY FK_40943D63E308AC6F');
        $this->addSql('ALTER TABLE material_category DROP FOREIGN KEY FK_40943D6312469DE2');
        $this->addSql('DROP INDEX idx_40943d63e308ac6f ON material_category');
        $this->addSql('CREATE INDEX IDX_25C2509CE308AC6F ON material_category (material_id)');
        $this->addSql('DROP INDEX idx_40943d6312469de2 ON material_category');
        $this->addSql('CREATE INDEX IDX_25C2509C650760A9 ON material_category (category_id)');
        $this->addSql('ALTER TABLE material_category ADD CONSTRAINT FK_40943D63E308AC6F FOREIGN KEY (material_id) REFERENCES material (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE material_category ADD CONSTRAINT FK_40943D6312469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE search_run DROP FOREIGN KEY FK_94CDD07F12469DE2');
        $this->addSql('DROP INDEX IDX_94CDD07F12469DE2 ON search_run');
        $this->addSql('ALTER TABLE search_run CHANGE category_id search_id INT NOT NULL');
        $this->addSql('ALTER TABLE search_run ADD CONSTRAINT FK_94CDD07F650760A9 FOREIGN KEY (search_id) REFERENCES category (id)');
        $this->addSql('CREATE INDEX IDX_94CDD07F650760A9 ON search_run (search_id)');
    }
}
