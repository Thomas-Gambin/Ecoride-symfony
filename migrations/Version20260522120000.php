<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260522120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'US8: profile_type, driver preferences, car registration unique constraint';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD COLUMN IF NOT EXISTS profile_type VARCHAR(32) DEFAULT \'passenger\' NOT NULL');

        $this->addSql('CREATE TABLE IF NOT EXISTS driver_preference (
            id SERIAL NOT NULL,
            user_id INT NOT NULL,
            allow_smoking BOOLEAN DEFAULT false NOT NULL,
            allow_animals BOOLEAN DEFAULT false NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS UNIQ_DRIVER_PREFERENCE_USER ON driver_preference (user_id)');
        $this->addSql(<<<'SQL'
DO $$ BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint c
        INNER JOIN pg_class rel ON rel.oid = c.conrelid
        WHERE rel.relname = 'driver_preference'
          AND c.contype = 'f'
    ) THEN
        ALTER TABLE driver_preference
            ADD CONSTRAINT fk_driver_preference_user
            FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
    END IF;
END $$;
SQL);

        $this->addSql('CREATE TABLE IF NOT EXISTS custom_preference (
            id SERIAL NOT NULL,
            driver_preference_id INT NOT NULL,
            label VARCHAR(120) NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS UNIQ_CUSTOM_PREFERENCE_LABEL_PER_DRIVER ON custom_preference (driver_preference_id, label)');
        $this->addSql(<<<'SQL'
DO $$ BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint c
        INNER JOIN pg_class rel ON rel.oid = c.conrelid
        WHERE rel.relname = 'custom_preference'
          AND c.contype = 'f'
    ) THEN
        ALTER TABLE custom_preference
            ADD CONSTRAINT fk_custom_preference_driver
            FOREIGN KEY (driver_preference_id) REFERENCES driver_preference (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
    END IF;
END $$;
SQL);

        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS UNIQ_CAR_REGISTRATION_NUMBER ON car (registration_number)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE custom_preference DROP CONSTRAINT IF EXISTS fk_custom_preference_driver');
        $this->addSql('ALTER TABLE custom_preference DROP CONSTRAINT IF EXISTS FK_CUSTOM_PREFERENCE_DRIVER');
        $this->addSql('DROP TABLE IF EXISTS custom_preference');
        $this->addSql('ALTER TABLE driver_preference DROP CONSTRAINT IF EXISTS fk_driver_preference_user');
        $this->addSql('ALTER TABLE driver_preference DROP CONSTRAINT IF EXISTS FK_DRIVER_PREFERENCE_USER');
        $this->addSql('DROP TABLE IF EXISTS driver_preference');
        $this->addSql('DROP INDEX IF EXISTS UNIQ_CAR_REGISTRATION_NUMBER');
        $this->addSql('ALTER TABLE "user" DROP COLUMN IF EXISTS profile_type');
    }
}
