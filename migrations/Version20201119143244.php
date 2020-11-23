<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201119143244 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE migration_versions');
        $this->addSql('ALTER TABLE material ADD creator_aut VARCHAR(255) DEFAULT NULL, ADD creator_cre VARCHAR(255) DEFAULT NULL, ADD contributor VARCHAR(255) DEFAULT NULL, ADD contributor_act VARCHAR(255) DEFAULT NULL, ADD contributor_aut VARCHAR(255) DEFAULT NULL, ADD contributor_ctb VARCHAR(255) DEFAULT NULL, ADD contributor_dkfig VARCHAR(255) DEFAULT NULL, ADD type VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE migration_versions (version VARCHAR(14) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, executed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(version)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE material DROP creator_aut, DROP creator_cre, DROP contributor, DROP contributor_act, DROP contributor_aut, DROP contributor_ctb, DROP contributor_dkfig, DROP type');
    }
}
