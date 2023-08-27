<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230827110257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add index on books.name';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX books_name_index ON books USING btree (name);');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX books_name_index ON books USING btree (name);');
    }
}
