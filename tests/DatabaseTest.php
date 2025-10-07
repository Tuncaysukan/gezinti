<?php

declare(strict_types=1);

namespace MiniORM\Tests;

use MiniORM\Database;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        Database::setConfig([
            'host' => $_ENV['DB_HOST'] ?? 'mysql',
            'port' => (int)($_ENV['DB_PORT'] ?? 3306),
            'database' => $_ENV['DB_NAME'] ?? 'mini_orm',
            'username' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? 'secret',
        ]);
    }

    protected function tearDown(): void
    {
        Database::resetConnection();
    }

    public function testCanConnectToDatabase(): void
    {
        $pdo = Database::getConnection();
        $this->assertInstanceOf(\PDO::class, $pdo);
    }

    public function testSingletonPattern(): void
    {
        $connection1 = Database::getConnection();
        $connection2 = Database::getConnection();
        
        $this->assertSame($connection1, $connection2);
    }

    public function testTransactionMethods(): void
    {
        $this->assertTrue(Database::beginTransaction());
        $this->assertTrue(Database::commit());
    }
}

