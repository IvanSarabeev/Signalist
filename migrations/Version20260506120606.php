<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260506120606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE alert (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, stock_symbol VARCHAR(10) NOT NULL, alert_name VARCHAR(150) NOT NULL, alert_type VARCHAR(20) NOT NULL, condition_quality VARCHAR(20) NOT NULL, threshold_value NUMERIC(12, 4) NOT NULL, frequency VARCHAR(30) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', last_triggered_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', INDEX IDX_17FD46C1A76ED395 (user_id), INDEX IDX_17FD46C126EEE46F (stock_symbol), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock (symbol VARCHAR(10) NOT NULL, name VARCHAR(100) NOT NULL, exchange VARCHAR(10) DEFAULT NULL, industry VARCHAR(50) DEFAULT NULL, logo_url VARCHAR(255) DEFAULT NULL, currency VARCHAR(5) DEFAULT NULL, cached_price NUMERIC(12, 4) DEFAULT NULL, cached_change_percent NUMERIC(6, 4) DEFAULT NULL, cached_previous_close NUMERIC(12, 4) DEFAULT NULL, cached_high NUMERIC(12, 4) DEFAULT NULL, cached_low NUMERIC(12, 4) DEFAULT NULL, quote_cached_at DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', PRIMARY KEY(symbol)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE watchlist_item (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, stock_symbol VARCHAR(10) NOT NULL, sort_order INT NOT NULL, added_at DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', INDEX IDX_1DEA83F6A76ED395 (user_id), INDEX IDX_1DEA83F626EEE46F (stock_symbol), UNIQUE INDEX UNIQ_1DEA83F6A76ED39526EEE46F (user_id, stock_symbol), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE alert ADD CONSTRAINT FK_17FD46C1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE alert ADD CONSTRAINT FK_17FD46C126EEE46F FOREIGN KEY (stock_symbol) REFERENCES stock (symbol)');
        $this->addSql('ALTER TABLE watchlist_item ADD CONSTRAINT FK_1DEA83F6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE watchlist_item ADD CONSTRAINT FK_1DEA83F626EEE46F FOREIGN KEY (stock_symbol) REFERENCES stock (symbol)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE alert DROP FOREIGN KEY FK_17FD46C1A76ED395');
        $this->addSql('ALTER TABLE alert DROP FOREIGN KEY FK_17FD46C126EEE46F');
        $this->addSql('ALTER TABLE watchlist_item DROP FOREIGN KEY FK_1DEA83F6A76ED395');
        $this->addSql('ALTER TABLE watchlist_item DROP FOREIGN KEY FK_1DEA83F626EEE46F');
        $this->addSql('DROP TABLE alert');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE watchlist_item');
    }
}
