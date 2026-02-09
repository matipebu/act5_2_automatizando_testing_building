<?php
// app/config/database.php

class Database {
    private string $driver;
    private string $host;
    private string $db;
    private string $user;
    private string $pass;
    private ?PDO $pdo = null;

    public function __construct()
    {
        $this->driver = $_ENV['DB_DRIVER'] ?? 'mysql';
        $this->host   = $_ENV['DB_HOST'] ?? 'localhost';
        $this->db     = $_ENV['DB_DATABASE'] ?? 'contacts';
        $this->user   = $_ENV['DB_USER'] ?? 'root';
        $this->pass   = $_ENV['DB_PASSWORD'] ?? 'root';
    }

    public function pdo(): PDO
    {
        if ($this->pdo) {
            return $this->pdo;
        }

        $maxRetries = 5;
        $retryDelay = 2; // segundos

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                if ($this->driver === 'sqlite') {
                    $dsn = 'sqlite:' . $this->db;
                    $this->pdo = new PDO($dsn);
                } else {
                    $dsn = "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4";
                    $this->pdo = new PDO($dsn, $this->user, $this->pass);
                }

                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                $this->bootstrap();
                return $this->pdo;
            } catch (PDOException $e) {
                if ($attempt < $maxRetries) {
                    error_log("DB connection attempt {$attempt} failed, retrying in {$retryDelay}s...");
                    sleep($retryDelay);
                    continue;
                }
                die('DB connection error: ' . $e->getMessage());
            }
        }
    }

    private function bootstrap(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS contacts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        // MySQL uses AUTO_INCREMENT, SQLite uses AUTOINCREMENT on INTEGER PRIMARY KEY
        if ($this->driver !== 'sqlite') {
            $sql = "CREATE TABLE IF NOT EXISTS contacts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(50) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        }

        $this->pdo->exec($sql);
    }
}
