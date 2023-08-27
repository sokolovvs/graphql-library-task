<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230827104120 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add index on authors.name';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX authors_name_index ON authors USING btree (name);');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX authors_name_index ON authors USING btree (name);');
    }
}
