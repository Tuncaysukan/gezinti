# Mini ORM

Laravel Eloquent benzeri, sıfırdan geliştirilmiş PHP ORM kütüphanesi.

## Gereksinimler

- PHP >= 8.1
- PDO Extension
- MySQL 8.0+
- Composer
- Docker & Docker Compose

## Kurulum

### 1. Projeyi klonlayın

```bash
git clone <https://github.com/Tuncaysukan/gezinti>
cd mini-orm
```

### 2. Composer bağımlılıklarını yükleyin

```bash
composer install
```

### 3. Docker ile veritabanını başlatın

```bash
docker-compose up -d
```

Bu komut MySQL container'ı başlatır ve otomatik olarak veritabanını hazırlar.

### 4. Test edin

```bash
docker exec -it mini_orm_php php example.php
```

---

## Kullanım

### Veritabanı Bağlantısı

```php
<?php

use MiniORM\Database;

Database::setConfig([
    'host' => 'mysql',
    'port' => 3306,
    'database' => 'mini_orm',
    'username' => 'root',
    'password' => 'secret',
]);
```

### Model Oluşturma

```php
<?php

namespace MiniORM\Models;

use MiniORM\Model;

class User extends Model
{
    protected string $table = 'users';
    
    protected array $fillable = [
        'name',
        'email',
        'password',
        'status',
        'age'
    ];
}
```

### CRUD İşlemleri

```php
// CREATE
$user = User::create([
    'name' => 'Ahmet Yılmaz',
    'email' => 'ahmet@example.com',
    'password' => password_hash('123456', PASSWORD_DEFAULT),
    'status' => 'active',
    'age' => 25
]);

// READ
$user = User::find(1);
$users = User::all();
$activeUsers = User::where('status', 'active')->get();

// UPDATE
$user = User::find(1);
$user->name = 'Yeni İsim';
$user->save();

// DELETE
$user->destroy();
```

### Query Builder

```php
// WHERE koşulları
$users = User::where('status', 'active')
    ->where('age', '>', 18)
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->get();

// Yardımcı metodlar
$count = User::count();
$exists = User::where('email', 'test@example.com')->exists();
$first = User::first();
```

### Model İlişkileri

```php
// BelongsTo
class Post extends Model
{
    public function user(): mixed
    {
        return $this->belongsTo(User::class);
    }
}

$post = Post::find(1);
$author = $post->user();

// HasMany
class User extends Model
{
    public function posts(): array
    {
        return $this->hasMany(Post::class);
    }
}

$user = User::find(1);
$posts = $user->posts();

// BelongsToMany
class User extends Model
{
    public function categories(): array
    {
        return $this->belongsToMany(Category::class, 'user_category');
    }
}

$user = User::find(1);
$categories = $user->categories();
```

### Transaction

```php
use MiniORM\Database;

try {
    Database::beginTransaction();
    
    $user = User::create([...]);
    $post = Post::create([...]);
    
    Database::commit();
} catch (Exception $e) {
    Database::rollBack();
}
```

### JOIN Sorguları

```php
use MiniORM\QueryBuilder;

$qb = new QueryBuilder();
$results = $qb->table('posts')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->select(['posts.title', 'users.name'])
    ->get();
```

---

## Test

```bash
# Testleri çalıştır
docker exec -it mini_orm_php vendor/bin/phpunit

# Örnek kodu çalıştır
docker exec -it mini_orm_php php example.php
```

---

Bu proje eğitim amaçlıdır. Production için Laravel Eloquent veya Doctrine kullanılması önerilir.
