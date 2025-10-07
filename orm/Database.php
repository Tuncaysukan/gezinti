<?php

declare(strict_types=1);

namespace MiniORM;

use PDO;
use PDOException;

/**
 * PDO bağlantı yöneticisi
 * Singleton pattern ile tek bir bağlantı sağlar
 */
class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    // Config ayarla
    public static function setConfig(array $config): void
    {
        self::$config = array_merge([
            'host' => 'localhost',
            'port' => 3306,
            'database' => 'mini_orm',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        ], $config);
    }

    // Bağlantı al - singleton pattern
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            if (empty(self::$config)) {
                self::loadConfigFromEnv();
            }

            try {
                $dsn = "mysql:host=" . self::$config['host'] . 
                       ";port=" . self::$config['port'] .
                       ";dbname=" . self::$config['database'] .
                       ";charset=" . self::$config['charset'];

                self::$instance = new PDO(
                    $dsn,
                    self::$config['username'],
                    self::$config['password'],
                    self::$config['options']
                );
            } catch (PDOException $e) {
                throw new \RuntimeException("DB bağlantı hatası: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    // .env dosyasını oku - basit bir parser
    private static function loadConfigFromEnv(): void
    {
        $envFile = __DIR__ . '/../.env';
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue; // yorum satırları
                
                list($key, $value) = explode('=', $line, 2);
                $_ENV[$key] = trim($value);
            }
        }

        self::$config = [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => (int)($_ENV['DB_PORT'] ?? 3306),
            'database' => $_ENV['DB_NAME'] ?? 'mini_orm',
            'username' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        ];
    }

    /**
     * Bağlantıyı sıfırla (test için kullanışlı)
     */
    public static function resetConnection(): void
    {
        self::$instance = null;
    }

    /**
     * Transaction başlat
     */
    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    /**
     * Transaction commit
     */
    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    /**
     * Transaction rollback
     */
    public static function rollBack(): bool
    {
        return self::getConnection()->rollBack();
    }
}

