<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260401211659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE milestones (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, amount NUMERIC(10, 2) NOT NULL, due_date DATE NOT NULL, status SMALLINT UNSIGNED DEFAULT 0 NOT NULL, review_note LONGTEXT DEFAULT NULL, submitted_at DATETIME DEFAULT NULL, reviewed_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, work_order_id INT NOT NULL, INDEX idx_milestone_work_order (work_order_id), INDEX idx_milestone_status (status), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE payments (id INT AUTO_INCREMENT NOT NULL, amount NUMERIC(10, 2) NOT NULL, status SMALLINT UNSIGNED DEFAULT 0 NOT NULL, paid_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, work_order_id INT NOT NULL, milestone_id INT NOT NULL, UNIQUE INDEX UNIQ_65D29B324B3E2EDA (milestone_id), INDEX idx_payment_status (status), INDEX idx_payment_work_order (work_order_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE work_orders (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, budget NUMERIC(10, 2) NOT NULL, status SMALLINT UNSIGNED DEFAULT 0 NOT NULL, deadline DATE NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, client_id INT NOT NULL, freelancer_id INT NOT NULL, INDEX IDX_4ED63BCC19EB6921 (client_id), INDEX IDX_4ED63BCC8545BDF5 (freelancer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE milestones ADD CONSTRAINT FK_18F78184582AE764 FOREIGN KEY (work_order_id) REFERENCES work_orders (id)');
        $this->addSql('ALTER TABLE payments ADD CONSTRAINT FK_65D29B32582AE764 FOREIGN KEY (work_order_id) REFERENCES work_orders (id)');
        $this->addSql('ALTER TABLE payments ADD CONSTRAINT FK_65D29B324B3E2EDA FOREIGN KEY (milestone_id) REFERENCES milestones (id)');
        $this->addSql('ALTER TABLE work_orders ADD CONSTRAINT FK_4ED63BCC19EB6921 FOREIGN KEY (client_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE work_orders ADD CONSTRAINT FK_4ED63BCC8545BDF5 FOREIGN KEY (freelancer_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE milestones DROP FOREIGN KEY FK_18F78184582AE764');
        $this->addSql('ALTER TABLE payments DROP FOREIGN KEY FK_65D29B32582AE764');
        $this->addSql('ALTER TABLE payments DROP FOREIGN KEY FK_65D29B324B3E2EDA');
        $this->addSql('ALTER TABLE work_orders DROP FOREIGN KEY FK_4ED63BCC19EB6921');
        $this->addSql('ALTER TABLE work_orders DROP FOREIGN KEY FK_4ED63BCC8545BDF5');
        $this->addSql('DROP TABLE milestones');
        $this->addSql('DROP TABLE payments');
        $this->addSql('DROP TABLE work_orders');
    }
}
