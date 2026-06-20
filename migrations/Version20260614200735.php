<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260614200735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Tighten alert enum columns and add CHECK constraints';
    }

    public function up(Schema $schema): void
    {
        // Tighten column lengths
        $this->addSql('ALTER TABLE alert MODIFY condition_quality VARCHAR(15) NOT NULL');
        $this->addSql('ALTER TABLE alert MODIFY frequency VARCHAR(15) NOT NULL');

        // Add CHECK constraints to enforce valid enum values at DB level
        $this->addSql("
            ALTER TABLE alert
            ADD CONSTRAINT chk_alert_type
            CHECK (alert_type IN (
                'price', 'percent_change', 'volume', 'market_cap',
                'moving_average', 'rsi', '52_week_high', '52_week_low'
            ))
        ");

        $this->addSql("
            ALTER TABLE alert
            ADD CONSTRAINT chk_condition_quality
            CHECK (condition_quality IN (
                'gt', 'gte', 'lt', 'lte', 'eq', 'crosses_above', 'crosses_below'
            ))
        ");

        $this->addSql("
            ALTER TABLE alert
            ADD CONSTRAINT chk_frequency
            CHECK (frequency IN (
                'once', 'every_time', 'once_per_hour',
                'once_per_day', 'once_per_week', 'market_open', 'market_close'
            ))
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE alert DROP CONSTRAINT chk_alert_type');
        $this->addSql('ALTER TABLE alert DROP CONSTRAINT chk_condition_quality');
        $this->addSql('ALTER TABLE alert DROP CONSTRAINT chk_frequency');

        $this->addSql('ALTER TABLE alert MODIFY condition_quality VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE alert MODIFY frequency VARCHAR(30) NOT NULL');
    }
}
