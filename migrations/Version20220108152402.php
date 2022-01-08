<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220108152402 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD uuid UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE product ALTER description DROP NOT NULL');
        $this->addSql('ALTER TABLE product ALTER slug TYPE VARCHAR(128)');
        $this->addSql('COMMENT ON COLUMN product.uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D34A04AD989D9B62 ON product (slug)');
        $this->addSql('UPDATE product SET uuid=uuid_generate_v4() WHERE uuid IS NULL'); // It's work ONLY with PostgresSQL
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_D34A04AD989D9B62');
        $this->addSql('ALTER TABLE product DROP uuid');
        $this->addSql('ALTER TABLE product ALTER description SET NOT NULL');
        $this->addSql('ALTER TABLE product ALTER slug TYPE VARCHAR(255)');
    }
}
