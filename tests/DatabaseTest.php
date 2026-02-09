<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/config/database.php';

class DatabaseTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        // Configure entorno de pruebas en memoria con SQLite
        $_ENV['DB_DRIVER'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_ENV['DB_HOST'] = 'localhost';
        $_ENV['DB_USER'] = 'root';
        $_ENV['DB_PASSWORD'] = '';

        $this->pdo = (new Database())->pdo();
    }

    public function testBootstrapCreatesContactsTable(): void
    {
        $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='contacts'");
        $table = $stmt->fetchColumn();

        $this->assertSame('contacts', $table, 'La tabla contacts debe existir tras el bootstrap');
    }

    public function testInsertAndListContacts(): void
    {
        $insert = $this->pdo->prepare('INSERT INTO contacts (name, email, phone) VALUES (?, ?, ?)');
        $insert->execute(['Alice', 'alice@example.com', '123']);

        $rows = $this->pdo->query('SELECT * FROM contacts')->fetchAll();

        $this->assertCount(1, $rows, 'Debe devolver un registro insertado');
        $this->assertSame('Alice', $rows[0]['name']);
        $this->assertSame('alice@example.com', $rows[0]['email']);
    }

    public function testDeleteContact(): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO contacts (name, email, phone) VALUES (?, ?, ?)');
        $stmt->execute(['Bob', 'bob@example.com', '456']);
        $id = $this->pdo->lastInsertId();

        $delete = $this->pdo->prepare('DELETE FROM contacts WHERE id = ?');
        $delete->execute([$id]);

        $count = $this->pdo->query('SELECT COUNT(*) FROM contacts')->fetchColumn();

        $this->assertSame('0', (string)$count, 'El contacto debe eliminarse correctamente');
    }
}
