<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use MiniORM\Database;
use MiniORM\QueryBuilder;
use MiniORM\Models\User;
use MiniORM\Models\Post;
use MiniORM\Models\Category;
use MiniORM\Models\Comment;

// Veritabanı yapılandırması
Database::setConfig([
    'host' => 'mysql',  // Docker container adı
    'port' => 3306,     // Container içindeki port
    'database' => 'mini_orm',
    'username' => 'root',
    'password' => 'secret',
]);

echo "=== Mini ORM Örnek Kullanımlar ===\n\n";

// 1. BASİT CRUD İŞLEMLERİ
echo "--- 1. Basit CRUD İşlemleri ---\n";

// CREATE
echo "\n1.1. Yeni kullanıcı oluştur:\n";
$newUser = User::create([
    'name' => 'Test Kullanıcı',
    'email' => 'test' . time() . '@example.com',
    'password' => password_hash('123456', PASSWORD_DEFAULT),
    'status' => 'active',
    'age' => 28
]);
echo "Yeni kullanıcı: ID={$newUser->id}, İsim={$newUser->name}\n";

// READ - Find
echo "\n1.2. ID ile kullanıcı bul:\n";
$user = User::find(1);
if ($user) {
    echo "Bulunan kullanıcı: {$user->name} ({$user->email})\n";
}

// READ - All
echo "\n1.3. Tüm kullanıcılar:\n";
$users = User::all();
echo "Toplam: " . count($users) . " kullanıcı\n";
for ($i = 0; $i < min(3, count($users)); $i++) {
    echo "- {$users[$i]->name} ({$users[$i]->email})\n";
}

// UPDATE
echo "\n1.4. Kullanıcı güncelle:\n";
$newUser->name = 'Güncellenmiş Kullanıcı';
$newUser->age = 30;
$newUser->save();
echo "Kullanıcı güncellendi: {$newUser->name}, Yaş: {$newUser->age}\n";

// DELETE
echo "\n1.5. Kullanıcı sil:\n";
$deleted = $newUser->destroy();
echo "Kullanıcı silindi: " . ($deleted ? 'Başarılı' : 'Başarısız') . "\n";

// 2. FLUENT QUERY BUILDER
echo "\n\n--- 2. Fluent Query Builder ---\n";

// WHERE ile filtreleme
echo "\n2.1. Aktif kullanıcıları getir:\n";
$activeUsers = User::where('status', '=', 'active')->get();
echo "Aktif kullanıcı sayısı: " . count($activeUsers) . "\n";

// Çoklu WHERE
echo "\n2.2. 25 yaşından büyük aktif kullanıcılar:\n";
$results = User::where('status', 'active')
    ->where('age', '>', 25)
    ->get();

foreach ($results as $row) {
    $u = new User($row);
    echo "- {$u->name}, Yaş: {$u->age}\n";
}

// ORDER BY ve LIMIT
echo "\n2.3. Son 3 kullanıcı (tarih sıralı):\n";
$latestUsers = User::query()
    ->orderBy('created_at', 'DESC')
    ->limit(3)
    ->get();

foreach ($latestUsers as $row) {
    $u = new User($row);
    echo "- {$u->name} (Oluşturma: {$u->created_at})\n";
}

// LIKE sorgusu
echo "\n2.4. Gmail kullanıcıları:\n";
$gmailUsers = User::where('email', 'like', '%gmail.com')->get();
echo "Gmail kullanan: " . count($gmailUsers) . " kullanıcı\n";

// COUNT
echo "\n2.5. Kullanıcı sayısı:\n";
echo "Toplam kullanıcı: " . User::count() . "\n";
echo "Aktif kullanıcı: " . User::where('status', 'active')->count() . "\n";

// EXISTS
echo "\n2.6. Kullanıcı var mı kontrol:\n";
$exists = User::where('email', 'ahmet@gmail.com')->exists();
echo "ahmet@gmail.com var mı? " . ($exists ? 'Evet' : 'Hayır') . "\n";

// FIRST
echo "\n2.7. İlk kullanıcıyı al:\n";
$firstUser = User::first();
if ($firstUser) {
    echo "İlk kullanıcı: {$firstUser->name}\n";
}

// 3. İLİŞKİLER
echo "\n\n--- 3. İlişkiler ---\n";

// BelongsTo (Çoka Bir)
echo "\n3.1. Post'un sahibini bul (belongsTo):\n";
$post = Post::find(1);
if ($post) {
    $owner = $post->user();
    echo "Post: {$post->title}\n";
    echo "Yazar: {$owner->name}\n";
    
    $category = $post->category();
    if ($category) {
        echo "Kategori: {$category->name}\n";
    }
}

// HasMany (Bire Çok)
echo "\n3.2. Kullanıcının postları (hasMany):\n";
$user = User::find(1);
if ($user) {
    $posts = $user->posts();
    echo "{$user->name} kullanıcısının {count($posts)} postu var:\n";
    foreach ($posts as $p) {
        echo "- {$p->title} (Durum: {$p->status})\n";
    }
}

// BelongsToMany (Çoka Çok)
echo "\n3.3. Kullanıcının kategorileri (belongsToMany):\n";
$user = User::find(1);
if ($user) {
    $categories = $user->categories();
    echo "{$user->name} kullanıcısının ilgi alanları:\n";
    foreach ($categories as $cat) {
        echo "- {$cat->name}\n";
    }
}

// Ters ilişki
echo "\n3.4. Kategorinin kullanıcıları:\n";
$category = Category::find(1);
if ($category) {
    $users = $category->users();
    echo "{$category->name} kategorisiyle ilgilenen kullanıcılar:\n";
    foreach ($users as $u) {
        echo "- {$u->name}\n";
    }
}

// 4. QUERY BUILDER (Bağımsız)
echo "\n\n--- 4. Query Builder (Bağımsız) ---\n";

$qb = new QueryBuilder();

echo "\n4.1. Manuel JOIN sorgusu:\n";
$results = $qb->table('posts')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->select(['posts.title', 'users.name as author'])
    ->limit(3)
    ->get();

foreach ($results as $row) {
    echo "- {$row['title']} (Yazar: {$row['author']})\n";
}

echo "\n4.2. Aggregation - Yorum sayısı:\n";
$qb->reset();
$commentCount = $qb->table('comments')->count();
echo "Toplam yorum sayısı: {$commentCount}\n";

// 5. TO ARRAY / TO JSON
echo "\n\n--- 5. Veri Dönüşümleri ---\n";

echo "\n5.1. toArray():\n";
$user = User::find(1);
if ($user) {
    $userArray = $user->toArray();
    echo "Array formatı:\n";
    print_r(array_slice($userArray, 0, 5, true));
}

echo "\n5.2. toJson():\n";
$user = User::find(1);
if ($user) {
    $userJson = $user->toJson(JSON_PRETTY_PRINT);
    echo "JSON formatı:\n";
    echo substr($userJson, 0, 200) . "...\n";
}

// 6. TRANSACTION
echo "\n\n--- 6. Transaction Örneği ---\n";

try {
    Database::beginTransaction();
    
    // Yeni kullanıcı oluştur
    $user = User::create([
        'name' => 'Transaction Test',
        'email' => 'transaction' . time() . '@test.com',
        'password' => password_hash('test', PASSWORD_DEFAULT),
        'status' => 'active'
    ]);
    
    echo "Transaction içinde kullanıcı oluşturuldu: ID={$user->id}\n";
    
    // Hemen sil (rollback için)
    $user->destroy();
    echo "Kullanıcı silindi\n";
    
    Database::commit();
    echo "Transaction başarıyla commit edildi\n";
    
} catch (Exception $e) {
    Database::rollBack();
    echo "Transaction rollback yapıldı: " . $e->getMessage() . "\n";
}

// 7. MAGIC METHODS
echo "\n\n--- 7. Magic Methods ---\n";

echo "\n7.1. Magic getter/setter:\n";
$user = new User();
$user->name = 'Magic User';
$user->email = 'magic@test.com';
echo "Name: {$user->name}\n";
echo "Email: {$user->email}\n";

echo "\n7.2. İlişki lazy loading:\n";
$post = Post::find(1);
if ($post) {
    // user() metodu çağrılmadan da erişim
    echo "Post yazarı: {$post->user()->name}\n";
}

echo "\n\n=== Örnekler Tamamlandı ===\n";

