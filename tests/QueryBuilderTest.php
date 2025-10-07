<?php

declare(strict_types=1);

namespace MiniORM\Tests;

use MiniORM\Database;
use MiniORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    protected QueryBuilder $builder;

    protected function setUp(): void
    {
        Database::setConfig([
            'host' => $_ENV['DB_HOST'] ?? 'mysql',
            'port' => (int)($_ENV['DB_PORT'] ?? 3306),
            'database' => $_ENV['DB_NAME'] ?? 'mini_orm',
            'username' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? 'secret',
        ]);

        $this->builder = new QueryBuilder();
    }

    protected function tearDown(): void
    {
        Database::resetConnection();
    }

    public function testSelectAll(): void
    {
        $results = $this->builder->table('users')->get();
        
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testWhereClause(): void
    {
        $results = $this->builder
            ->table('users')
            ->where('status', '=', 'active')
            ->get();
        
        $this->assertIsArray($results);
        
        foreach ($results as $user) {
            $this->assertEquals('active', $user['status']);
        }
    }

    public function testMultipleWheres(): void
    {
        $results = $this->builder
            ->table('users')
            ->where('status', '=', 'active')
            ->where('age', '>', 20)
            ->get();
        
        $this->assertIsArray($results);
    }

    public function testOrderBy(): void
    {
        $results = $this->builder
            ->table('users')
            ->orderBy('created_at', 'DESC')
            ->get();
        
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testLimit(): void
    {
        $results = $this->builder
            ->table('users')
            ->limit(2)
            ->get();
        
        $this->assertIsArray($results);
        $this->assertCount(2, $results);
    }

    public function testFirst(): void
    {
        $result = $this->builder
            ->table('users')
            ->where('status', '=', 'active')
            ->first();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
    }

    public function testCount(): void
    {
        $count = $this->builder
            ->table('users')
            ->where('status', '=', 'active')
            ->count();
        
        $this->assertIsInt($count);
        $this->assertGreaterThan(0, $count);
    }

    public function testExists(): void
    {
        $exists = $this->builder
            ->table('users')
            ->where('email', '=', 'ahmet@gmail.com')
            ->exists();
        
        $this->assertTrue($exists);
    }

    public function testInsert(): void
    {
        $id = $this->builder
            ->table('users')
            ->insert([
                'name' => 'Test User',
                'email' => 'test' . time() . '@test.com',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'status' => 'active',
                'age' => 25
            ]);
        
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
        
        // Temizlik
        $this->builder->reset()->table('users')->where('id', '=', $id)->delete();
    }

    public function testUpdate(): void
    {
        // Önce bir kayıt oluştur
        $id = $this->builder
            ->table('users')
            ->insert([
                'name' => 'Update Test',
                'email' => 'update' . time() . '@test.com',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'status' => 'active'
            ]);
        
        // Şimdi güncelle
        $affected = $this->builder
            ->reset()
            ->table('users')
            ->where('id', '=', $id)
            ->update(['name' => 'Updated Name']);
        
        $this->assertEquals(1, $affected);
        
        // Temizlik
        $this->builder->reset()->table('users')->where('id', '=', $id)->delete();
    }

    public function testDelete(): void
    {
        // Önce bir kayıt oluştur
        $id = $this->builder
            ->table('users')
            ->insert([
                'name' => 'Delete Test',
                'email' => 'delete' . time() . '@test.com',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'status' => 'active'
            ]);
        
        // Şimdi sil
        $affected = $this->builder
            ->reset()
            ->table('users')
            ->where('id', '=', $id)
            ->delete();
        
        $this->assertEquals(1, $affected);
    }

    public function testJoin(): void
    {
        $results = $this->builder
            ->table('posts')
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->select(['posts.*', 'users.name as user_name'])
            ->get();
        
        $this->assertIsArray($results);
    }
}

