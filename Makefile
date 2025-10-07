.PHONY: help install up down test example shell clean

help: ## Yardım menüsü
	@echo "Mini ORM - Kullanılabilir komutlar:"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

install: ## Composer bağımlılıklarını yükle
	composer install

up: ## Docker containerları başlat
	docker-compose up -d
	@echo "Veritabanı hazırlanıyor... (5 saniye bekleyin)"
	@sleep 5
	@echo "✓ MySQL çalışıyor: localhost:3307"
	@echo "✓ PHPMyAdmin: http://localhost:8080"

down: ## Docker containerları durdur
	docker-compose down

test: ## PHPUnit testlerini çalıştır
	docker exec -it mini_orm_php vendor/bin/phpunit

example: ## Örnek kod çalıştır
	docker exec -it mini_orm_php php example.php

shell: ## PHP container'a bağlan
	docker exec -it mini_orm_php bash

mysql: ## MySQL CLI'ya bağlan
	docker exec -it mini_orm_mysql mysql -uroot -psecret mini_orm

clean: ## Docker volume ve cache temizle
	docker-compose down -v
	rm -rf mysql-data vendor .phpunit.cache

logs: ## Docker loglarını göster
	docker-compose logs -f

restart: down up ## Container'ları yeniden başlat

