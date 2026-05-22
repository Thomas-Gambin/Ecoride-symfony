<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260522130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Restore user profile_type column dropped by accidental schema diff.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE \"user\" ADD COLUMN IF NOT EXISTS profile_type VARCHAR(32) DEFAULT 'passenger' NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" DROP COLUMN IF EXISTS profile_type');
    }
}
