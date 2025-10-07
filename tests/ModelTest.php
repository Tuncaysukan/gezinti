<?php

declare(strict_types=1);

namespace MiniORM\Tests;

use MiniORM\Database;
use MiniORM\Models\User;
use MiniORM\Models\Post;
use MiniORM\Models\Category;
use MiniORM\Models\Comment;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
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

    public function testAll(): void
    {
        $users = User::all();
        
        $this->assertIsArray($users);
        $this->assertNotEmpty($users);
        $this->assertInstanceOf(User::class, $users[0]);
    }

    public function testFind(): void
    {
        $user = User::find(1);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(1, $user->id);
    }

    public function testWhere(): void
    {
        $users = User::where('status', '=', 'active')->get();
        
        $this->assertIsArray($users);
    }

    public function testCreate(): void
    {
        $user = User::create([
            'name' => 'New User',
            'email' => 'newuser' . time() . '@test.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'status' => 'active',
            'age' => 30
        ]);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->id);
        
        // Temizlik
        $user->destroy();
    }

    public function testUpdate(): void
    {
        $user = User::create([
            'name' => 'Update Test',
            'email' => 'updatetest' . time() . '@test.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'status' => 'active'
        ]);
        
        $user->name = 'Updated Name';
        $user->save();
        
        $updated = User::find($user->id);
        $this->assertEquals('Updated Name', $updated->name);
        
        // Temizlik
        $user->destroy();
    }

    public function testDelete(): void
    {
        $user = User::create([
            'name' => 'Delete Test',
            'email' => 'deletetest' . time() . '@test.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'status' => 'active'
        ]);
        
        $id = $user->id;
        $user->destroy();
        
        $deleted = User::find($id);
        $this->assertNull($deleted);
    }

    public function testCount(): void
    {
        $count = User::count();
        
        $this->assertIsInt($count);
        $this->assertGreaterThan(0, $count);
    }

    public function testToArray(): void
    {
        $user = User::find(1);
        $array = $user->toArray();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
    }

    public function testToJson(): void
    {
        $user = User::find(1);
        $json = $user->toJson();
        
        $this->assertJson($json);
        
        $decoded = json_decode($json, true);
        $this->assertArrayHasKey('id', $decoded);
    }

    public function testBelongsToRelation(): void
    {
        $post = Post::find(1);
        $user = $post->user();
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($post->user_id, $user->id);
    }

    public function testHasManyRelation(): void
    {
        $user = User::find(1);
        $posts = $user->posts();
        
        $this->assertIsArray($posts);
        
        if (!empty($posts)) {
            $this->assertInstanceOf(Post::class, $posts[0]);
        }
    }

    public function testBelongsToManyRelation(): void
    {
        $user = User::find(1);
        $categories = $user->categories();
        
        $this->assertIsArray($categories);
        
        if (!empty($categories)) {
            $this->assertInstanceOf(Category::class, $categories[0]);
        }
    }

    public function testMagicGetter(): void
    {
        $user = User::find(1);
        
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
    }

    public function testMagicSetter(): void
    {
        $user = new User();
        $user->name = 'Magic Test';
        
        $this->assertEquals('Magic Test', $user->name);
    }
}

