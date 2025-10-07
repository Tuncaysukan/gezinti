# Katkıda Bulunma Rehberi

Mini ORM projesine katkıda bulunmayı düşündüğünüz için teşekkürler! 🎉

## Nasıl Katkıda Bulunabilirsiniz?

### 1. Issue Açma

- Bug buldunuz mu? Issue açın ve detaylı açıklayın
- Yeni özellik önerisi? Feature request issue'su açın
- Dokümantasyon hatası? Documentation issue'su açın

### 2. Pull Request Gönderme

#### Adımlar:

1. **Fork yapın**
   ```bash
   # GitHub'da "Fork" butonuna tıklayın
   ```

2. **Projeyi klonlayın**
   ```bash
   git clone https://github.com/KULLANICI_ADINIZ/mini-orm.git
   cd mini-orm
   ```

3. **Feature branch oluşturun**
   ```bash
   git checkout -b feature/amazing-feature
   ```

4. **Geliştirme yapın**
   - Kod yazın
   - Test ekleyin
   - Dokümantasyonu güncelleyin

5. **Testleri çalıştırın**
   ```bash
   make test
   ```

6. **Commit edin**
   ```bash
   git add .
   git commit -m "feat: Add amazing feature"
   ```

7. **Push edin**
   ```bash
   git push origin feature/amazing-feature
   ```

8. **Pull Request açın**
   - GitHub'da pull request oluşturun
   - Değişikliklerinizi detaylı açıklayın

## Kod Standartları

### PHP Standartları

- PSR-12 kod standardına uyun
- `declare(strict_types=1);` kullanın
- Type hints ekleyin
- DocBlock yorumları yazın

### Commit Mesajları

Conventional Commits formatını kullanın:

```
feat: Yeni özellik ekle
fix: Bug düzelt
docs: Dokümantasyon güncelle
test: Test ekle
refactor: Kod refactor
style: Kod formatı düzelt
chore: Yapılandırma değişikliği
```

### Test Yazma

Her yeni özellik için test yazın:

```php
public function testNewFeature(): void
{
    // Arrange
    $user = User::create([...]);
    
    // Act
    $result = $user->newFeature();
    
    // Assert
    $this->assertNotNull($result);
}
```

## Geliştirme Ortamı

```bash
# Kurulum
bash setup.sh

# Geliştirme
make up      # Docker başlat
make shell   # Container'a bağlan
make test    # Testleri çalıştır
make example # Örnek çalıştır

# Temizlik
make down    # Durdur
make clean   # Tamamen temizle
```

## İyileştirme Fikirleri

Katkıda bulunabileceğiniz alanlar:

- [ ] Eager loading tam implementasyonu
- [ ] Soft delete özelliği
- [ ] Query caching
- [ ] Migration builder
- [ ] Database seeder
- [ ] Model events
- [ ] Accessor & Mutator
- [ ] Pagination
- [ ] whereIn, whereNull, whereBetween metodları
- [ ] Dokümantasyon iyileştirmeleri
- [ ] Daha fazla test coverage

## Code Review Süreci

1. Pull request açıldığında otomatik testler çalışır
2. En az 1 maintainer review yapar
3. Gerekli değişiklikler yapılır
4. Onaylandıktan sonra merge edilir

## İletişim

- GitHub Issues: Sorularınız için
- Pull Requests: Kod katkıları için

## Davranış Kuralları

- Saygılı olun
- Yapıcı eleştiri yapın
- Farklı fikirlere açık olun
- Yardımsever olun

Teşekkürler! 🙏

