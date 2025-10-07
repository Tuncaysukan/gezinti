# Changelog

Tüm önemli değişiklikler bu dosyada belgelenecektir.

## [1.0.0] - 2024-10-06

### Eklenenler

- ✨ PDO tabanlı Database bağlantı yöneticisi (Singleton pattern)
- ✨ Fluent Query Builder (zincirleme metodlar)
- ✨ Abstract Model sınıfı (Active Record pattern)
- ✨ CRUD operasyonları (Create, Read, Update, Delete)
- ✨ Model ilişkileri:
  - belongsTo (Many-to-One)
  - hasMany (One-to-Many)
  - hasOne (One-to-One)
  - belongsToMany (Many-to-Many)
- ✨ SQL injection koruması (PDO prepared statements)
- ✨ Transaction desteği
- ✨ Magic methods (__get, __set, __isset)
- ✨ toArray() ve toJson() dönüşüm metodları
- ✨ Query builder metodları:
  - where(), orWhere()
  - orderBy()
  - limit(), offset()
  - join(), leftJoin()
  - count(), exists(), first()
- ✨ Örnek modeller (User, Post, Category, Comment)
- ✨ Docker ve docker-compose desteği
- ✨ PHPUnit test suite (45+ test)
- ✨ Otomatik database migration (init.sql)
- ✨ Örnek seed verileri
- ✨ Makefile ile kolay komutlar
- ✨ Kurulum scripti (setup.sh)
- ✨ Kapsamlı dokümantasyon

### Güvenlik

- 🔒 Tüm SQL sorguları parametrik (prepared statements)
- 🔒 Mass assignment koruması ($fillable)
- 🔒 PDO ERRMODE_EXCEPTION aktif

### Performans

- ⚡ Singleton connection pattern
- ⚡ Lazy loading ilişkiler
- ⚡ Query builder optimizasyonları

### Dokümantasyon

- 📝 README.md (Türkçe)
- 📝 CONTRIBUTING.md
- 📝 CHANGELOG.md
- 📝 LICENSE (MIT)
- 📝 Kod içi DocBlock yorumları
- 📝 example.php (çalışan örnekler)

## [Gelecek Sürümler]

### Planlanan Özellikler

- [ ] v1.1.0 - Eager loading (with()) tam implementasyonu
- [ ] v1.2.0 - Soft delete özelliği
- [ ] v1.3.0 - Query caching
- [ ] v1.4.0 - Model events (creating, created, updating, updated, etc.)
- [ ] v1.5.0 - Accessor & Mutator desteği
- [ ] v2.0.0 - Migration builder ve seeder sistemi

---

Format: [Keep a Changelog](https://keepachangelog.com/)  
Versiyon: [Semantic Versioning](https://semver.org/)

