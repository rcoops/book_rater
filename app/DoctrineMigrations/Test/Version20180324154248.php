<?php declare(strict_types = 1);

namespace Application\Test\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180324154248 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_6970EB0FA76ED395');
        $this->addSql('DROP INDEX IDX_6970EB0F16A2B381');
        $this->addSql('CREATE TEMPORARY TABLE __temp__reviews AS SELECT id, book_id, user_id, title, comments, rating, created_date, edited_date FROM reviews');
        $this->addSql('DROP TABLE reviews');
        $this->addSql('CREATE TABLE reviews (id INTEGER NOT NULL, book_id INTEGER DEFAULT NULL, user_id INTEGER DEFAULT NULL, title VARCHAR(255) NOT NULL COLLATE BINARY, comments CLOB NOT NULL COLLATE BINARY, rating INTEGER NOT NULL, created_date DATETIME NOT NULL, edited_date DATETIME DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_6970EB0F16A2B381 FOREIGN KEY (book_id) REFERENCES books (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6970EB0FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO reviews (id, book_id, user_id, title, comments, rating, created_date, edited_date) SELECT id, book_id, user_id, title, comments, rating, created_date, edited_date FROM __temp__reviews');
        $this->addSql('DROP TABLE __temp__reviews');
        $this->addSql('CREATE INDEX IDX_6970EB0FA76ED395 ON reviews (user_id)');
        $this->addSql('CREATE INDEX IDX_6970EB0F16A2B381 ON reviews (book_id)');
        $this->addSql('DROP INDEX IDX_2CAD560EA76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__api_tokens AS SELECT id, user_id, token, notes FROM api_tokens');
        $this->addSql('DROP TABLE api_tokens');
        $this->addSql('CREATE TABLE api_tokens (id INTEGER NOT NULL, user_id INTEGER NOT NULL, token VARCHAR(100) NOT NULL COLLATE BINARY, notes CLOB NOT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_2CAD560EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO api_tokens (id, user_id, token, notes) SELECT id, user_id, token, notes FROM __temp__api_tokens');
        $this->addSql('DROP TABLE __temp__api_tokens');
        $this->addSql('CREATE INDEX IDX_2CAD560EA76ED395 ON api_tokens (user_id)');
        $this->addSql('DROP INDEX IDX_DB021E96A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__messages AS SELECT id, user_id, subject, message, created_date, is_read FROM messages');
        $this->addSql('DROP TABLE messages');
        $this->addSql('CREATE TABLE messages (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, subject VARCHAR(255) NOT NULL COLLATE BINARY, message CLOB NOT NULL COLLATE BINARY, created_date DATETIME NOT NULL, is_read BOOLEAN DEFAULT \'0\' NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_DB021E96A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO messages (id, user_id, subject, message, created_date, is_read) SELECT id, user_id, subject, message, created_date, is_read FROM __temp__messages');
        $this->addSql('DROP TABLE __temp__messages');
        $this->addSql('CREATE INDEX IDX_DB021E96A76ED395 ON messages (user_id)');
        $this->addSql('DROP INDEX IDX_5C930A5F16A2B381');
        $this->addSql('DROP INDEX IDX_5C930A5FF675F31B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__author_books AS SELECT author_id, book_id FROM author_books');
        $this->addSql('DROP TABLE author_books');
        $this->addSql('CREATE TABLE author_books (author_id INTEGER NOT NULL, book_id INTEGER NOT NULL, PRIMARY KEY(author_id, book_id), CONSTRAINT FK_5C930A5FF675F31B FOREIGN KEY (author_id) REFERENCES authors (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5C930A5F16A2B381 FOREIGN KEY (book_id) REFERENCES books (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO author_books (author_id, book_id) SELECT author_id, book_id FROM __temp__author_books');
        $this->addSql('DROP TABLE __temp__author_books');
        $this->addSql('CREATE INDEX IDX_5C930A5F16A2B381 ON author_books (book_id)');
        $this->addSql('CREATE INDEX IDX_5C930A5FF675F31B ON author_books (author_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_2CAD560EA76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__api_tokens AS SELECT id, user_id, token, notes FROM api_tokens');
        $this->addSql('DROP TABLE api_tokens');
        $this->addSql('CREATE TABLE api_tokens (id INTEGER NOT NULL, user_id INTEGER NOT NULL, token VARCHAR(100) NOT NULL, notes CLOB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO api_tokens (id, user_id, token, notes) SELECT id, user_id, token, notes FROM __temp__api_tokens');
        $this->addSql('DROP TABLE __temp__api_tokens');
        $this->addSql('CREATE INDEX IDX_2CAD560EA76ED395 ON api_tokens (user_id)');
        $this->addSql('DROP INDEX IDX_5C930A5FF675F31B');
        $this->addSql('DROP INDEX IDX_5C930A5F16A2B381');
        $this->addSql('CREATE TEMPORARY TABLE __temp__author_books AS SELECT author_id, book_id FROM author_books');
        $this->addSql('DROP TABLE author_books');
        $this->addSql('CREATE TABLE author_books (author_id INTEGER NOT NULL, book_id INTEGER NOT NULL, PRIMARY KEY(author_id, book_id))');
        $this->addSql('INSERT INTO author_books (author_id, book_id) SELECT author_id, book_id FROM __temp__author_books');
        $this->addSql('DROP TABLE __temp__author_books');
        $this->addSql('CREATE INDEX IDX_5C930A5FF675F31B ON author_books (author_id)');
        $this->addSql('CREATE INDEX IDX_5C930A5F16A2B381 ON author_books (book_id)');
        $this->addSql('DROP INDEX IDX_DB021E96A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__messages AS SELECT id, user_id, subject, message, created_date, is_read FROM messages');
        $this->addSql('DROP TABLE messages');
        $this->addSql('CREATE TABLE messages (id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, subject VARCHAR(255) NOT NULL, message CLOB NOT NULL, created_date DATETIME NOT NULL, is_read BOOLEAN DEFAULT \'0\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO messages (id, user_id, subject, message, created_date, is_read) SELECT id, user_id, subject, message, created_date, is_read FROM __temp__messages');
        $this->addSql('DROP TABLE __temp__messages');
        $this->addSql('CREATE INDEX IDX_DB021E96A76ED395 ON messages (user_id)');
        $this->addSql('DROP INDEX IDX_6970EB0F16A2B381');
        $this->addSql('DROP INDEX IDX_6970EB0FA76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__reviews AS SELECT id, book_id, user_id, title, comments, rating, created_date, edited_date FROM reviews');
        $this->addSql('DROP TABLE reviews');
        $this->addSql('CREATE TABLE reviews (id INTEGER NOT NULL, book_id INTEGER DEFAULT NULL, user_id INTEGER DEFAULT NULL, title VARCHAR(255) NOT NULL, comments CLOB NOT NULL, rating INTEGER NOT NULL, created_date DATETIME NOT NULL, edited_date DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO reviews (id, book_id, user_id, title, comments, rating, created_date, edited_date) SELECT id, book_id, user_id, title, comments, rating, created_date, edited_date FROM __temp__reviews');
        $this->addSql('DROP TABLE __temp__reviews');
        $this->addSql('CREATE INDEX IDX_6970EB0F16A2B381 ON reviews (book_id)');
        $this->addSql('CREATE INDEX IDX_6970EB0FA76ED395 ON reviews (user_id)');
    }
}