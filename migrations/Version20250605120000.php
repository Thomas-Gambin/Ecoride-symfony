<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250605120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Extend carpool entity for US9 trip creation (city codes, arrival time, platform fee, timestamps).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE carpool ADD departure_city_code VARCHAR(5) NOT NULL DEFAULT \'00000\'');
        $this->addSql('ALTER TABLE carpool ADD departure_postal_code VARCHAR(10) NOT NULL DEFAULT \'00000\'');
        $this->addSql('ALTER TABLE carpool ADD arrival_city_code VARCHAR(5) NOT NULL DEFAULT \'00000\'');
        $this->addSql('ALTER TABLE carpool ADD arrival_postal_code VARCHAR(10) NOT NULL DEFAULT \'00000\'');
        $this->addSql('ALTER TABLE carpool ADD arrival_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE carpool ADD platform_fee_credits INT NOT NULL DEFAULT 2');
        $this->addSql('ALTER TABLE carpool ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE carpool ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');

        $this->addSql('ALTER TABLE carpool ALTER COLUMN departure_city_code DROP DEFAULT');
        $this->addSql('ALTER TABLE carpool ALTER COLUMN departure_postal_code DROP DEFAULT');
        $this->addSql('ALTER TABLE carpool ALTER COLUMN arrival_city_code DROP DEFAULT');
        $this->addSql('ALTER TABLE carpool ALTER COLUMN arrival_postal_code DROP DEFAULT');
        $this->addSql('ALTER TABLE carpool ALTER COLUMN arrival_time DROP DEFAULT');
        $this->addSql('ALTER TABLE carpool ALTER COLUMN platform_fee_credits DROP DEFAULT');
        $this->addSql('ALTER TABLE carpool ALTER COLUMN created_at DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE carpool DROP departure_city_code');
        $this->addSql('ALTER TABLE carpool DROP departure_postal_code');
        $this->addSql('ALTER TABLE carpool DROP arrival_city_code');
        $this->addSql('ALTER TABLE carpool DROP arrival_postal_code');
        $this->addSql('ALTER TABLE carpool DROP arrival_time');
        $this->addSql('ALTER TABLE carpool DROP platform_fee_credits');
        $this->addSql('ALTER TABLE carpool DROP created_at');
        $this->addSql('ALTER TABLE carpool DROP updated_at');
    }
}
