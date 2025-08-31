<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version202508310406AddImageToEvenement extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add image column to evenement table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE evenement ADD image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE evenement DROP image');
    }
}