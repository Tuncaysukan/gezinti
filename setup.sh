#!/bin/bash

echo "==================================="
echo "Mini ORM Kurulum Scripti"
echo "==================================="
echo ""

# Renk kodları
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Composer kontrolü
echo -e "${YELLOW}1. Composer kontrol ediliyor...${NC}"
if ! command -v composer &> /dev/null; then
    echo -e "${RED}✗ Composer bulunamadı. Lütfen Composer'ı yükleyin: https://getcomposer.org/${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Composer bulundu${NC}"
echo ""

# Docker kontrolü
echo -e "${YELLOW}2. Docker kontrol ediliyor...${NC}"
if ! command -v docker &> /dev/null; then
    echo -e "${RED}✗ Docker bulunamadı. Lütfen Docker'ı yükleyin: https://docker.com/${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Docker bulundu${NC}"
echo ""

# Docker Compose kontrolü
echo -e "${YELLOW}3. Docker Compose kontrol ediliyor...${NC}"
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}✗ Docker Compose bulunamadı. Lütfen Docker Compose'u yükleyin${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Docker Compose bulundu${NC}"
echo ""

# Composer install
echo -e "${YELLOW}4. Composer bağımlılıkları yükleniyor...${NC}"
composer install
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Composer bağımlılıkları yüklendi${NC}"
else
    echo -e "${RED}✗ Composer install başarısız${NC}"
    exit 1
fi
echo ""

# .env dosyası oluştur
echo -e "${YELLOW}5. .env dosyası oluşturuluyor...${NC}"
if [ ! -f .env ]; then
    cat > .env << EOF
DB_HOST=localhost
DB_PORT=3307
DB_NAME=mini_orm
DB_USER=root
DB_PASSWORD=secret
EOF
    echo -e "${GREEN}✓ .env dosyası oluşturuldu${NC}"
else
    echo -e "${YELLOW}! .env dosyası zaten var, atlanıyor${NC}"
fi
echo ""

# Docker başlat
echo -e "${YELLOW}6. Docker container'ları başlatılıyor...${NC}"
docker-compose up -d
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Docker container'ları başlatıldı${NC}"
else
    echo -e "${RED}✗ Docker başlatma başarısız${NC}"
    exit 1
fi
echo ""

# Veritabanının hazır olmasını bekle
echo -e "${YELLOW}7. Veritabanı hazırlanıyor...${NC}"
echo "   (Yaklaşık 10 saniye bekleyiniz)"
sleep 10
echo -e "${GREEN}✓ Veritabanı hazır${NC}"
echo ""

# Test çalıştır
echo -e "${YELLOW}8. Kurulum test ediliyor...${NC}"
docker exec -it mini_orm_php php -v > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ PHP container çalışıyor${NC}"
else
    echo -e "${RED}✗ PHP container çalışmıyor${NC}"
fi
echo ""

# Başarılı mesajı
echo -e "${GREEN}==================================="
echo "✓ Kurulum tamamlandı!"
echo "===================================${NC}"
echo ""
echo "Kullanılabilir komutlar:"
echo ""
echo "  make example    - Örnek kod çalıştır"
echo "  make test       - Testleri çalıştır"
echo "  make shell      - PHP container'a bağlan"
echo "  make logs       - Logları görüntüle"
echo "  make down       - Container'ları durdur"
echo ""
echo "Veritabanı erişim bilgileri:"
echo "  Host: localhost"
echo "  Port: 3307"
echo "  Database: mini_orm"
echo "  Username: root"
echo "  Password: secret"
echo ""
echo "PHPMyAdmin: http://localhost:8080"
echo ""
echo "İlk testiniz için: make example"
echo ""

