# 🚀 Hızlı Kurulum Rehberi

## Windows Kurulumu

### 1. Gereksinimleri Yükleyin

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows için)
- [Composer](https://getcomposer.org/download/) (Windows için)
- [Git](https://git-scm.com/downloads)

### 2. Projeyi Klonlayın

```bash
git clone <repo-url>
cd mini-orm
```

### 3. Composer Bağımlılıklarını Yükleyin

```bash
composer install
```

### 4. Docker'ı Başlatın

```bash
docker-compose up -d
```

### 5. Bekleyin (Önemli!)

Veritabanının hazır olması için yaklaşık 10-15 saniye bekleyin.

### 6. Test Edin

```bash
# Örnek kod çalıştır
docker exec -it mini_orm_php php example.php

# Testleri çalıştır
docker exec -it mini_orm_php vendor/bin/phpunit
```

---

## Linux/Mac Kurulumu

### Otomatik Kurulum (Önerilen)

```bash
bash setup.sh
```

### Manuel Kurulum

```bash
# 1. Composer install
composer install

# 2. .env oluştur
cp .env.example .env

# 3. Docker başlat
docker-compose up -d

# 4. Bekle
sleep 10

# 5. Test et
make example
make test
```

---

## Makefile Komutları (Linux/Mac)

```bash
make help      # Yardım menüsü
make install   # Composer install
make up        # Docker başlat
make down      # Docker durdur
make test      # Testleri çalıştır
make example   # Örnek kodu çalıştır
make shell     # PHP container'a bağlan
make mysql     # MySQL CLI'ya bağlan
make logs      # Logları göster
make clean     # Temizle
make restart   # Yeniden başlat
```

---

## Windows'ta Makefile Olmadan Çalıştırma

```powershell
# Docker başlat
docker-compose up -d

# Composer install
composer install

# Örnek çalıştır
docker exec -it mini_orm_php php example.php

# Test çalıştır
docker exec -it mini_orm_php vendor/bin/phpunit

# Shell'e bağlan
docker exec -it mini_orm_php bash

# MySQL'e bağlan
docker exec -it mini_orm_mysql mysql -uroot -psecret mini_orm

# Docker durdur
docker-compose down

# Temizle
docker-compose down -v
```

---

## Veritabanı Bilgileri

- **Host:** localhost
- **Port:** 3307 (Docker'da 3306 yerine çakışma olmasın diye)
- **Database:** mini_orm
- **Username:** root
- **Password:** secret

### PHPMyAdmin

http://localhost:8080

---

## Sorun Giderme

### Problem: "Port 3307 zaten kullanımda"

**Çözüm:** `docker-compose.yml` dosyasında port'u değiştirin:

```yaml
ports:
  - "3308:3306"  # 3307 yerine 3308
```

### Problem: "Connection refused"

**Çözüm:** Veritabanının hazır olmasını bekleyin (10-15 saniye).

### Problem: "Composer bulunamadı"

**Çözüm:** Composer'ı yükleyin: https://getcomposer.org/

### Problem: "Docker çalışmıyor"

**Çözüm:** Docker Desktop'ı başlatın ve çalıştığından emin olun.

### Problem: Testler başarısız

**Çözüm 1:** Veritabanının hazır olduğundan emin olun:
```bash
docker exec -it mini_orm_mysql mysql -uroot -psecret -e "SHOW DATABASES;"
```

**Çözüm 2:** Container'ları yeniden başlatın:
```bash
docker-compose down
docker-compose up -d
sleep 10
```

---

## İlk Testiniz

```bash
# Örnek kodu çalıştırın
docker exec -it mini_orm_php php example.php
```

Bu komut şunları test eder:
- ✅ Veritabanı bağlantısı
- ✅ CRUD operasyonları
- ✅ Fluent Query Builder
- ✅ Model ilişkileri
- ✅ Transaction
- ✅ Magic methods

Eğer örnek kod çalışıyorsa, kurulum başarılıdır! 🎉

---

## Sonraki Adımlar

1. [README.md](README.md) dosyasını okuyun
2. [example.php](example.php) kodlarını inceleyin
3. Kendi modellerinizi oluşturun
4. Testleri çalıştırın: `make test` veya `docker exec -it mini_orm_php vendor/bin/phpunit`
5. [CONTRIBUTING.md](CONTRIBUTING.md) dosyasına göz atın

---

## Hızlı Başlangıç Kodu

```php
<?php

require_once 'vendor/autoload.php';

use MiniORM\Database;
use MiniORM\Models\User;

// Veritabanı bağlantısı
Database::setConfig([
    'host' => 'localhost',
    'port' => 3307,
    'database' => 'mini_orm',
    'username' => 'root',
    'password' => 'secret',
]);

// Kullanıcı oluştur
$user = User::create([
    'name' => 'Ahmet',
    'email' => 'ahmet@example.com',
    'password' => password_hash('123456', PASSWORD_DEFAULT),
    'status' => 'active',
    'age' => 25
]);

echo "Kullanıcı oluşturuldu: {$user->id}\n";

// Kullanıcıları listele
$users = User::where('status', 'active')->get();
echo "Aktif kullanıcı sayısı: " . count($users) . "\n";
```

---

**Happy Coding! 🚀**

