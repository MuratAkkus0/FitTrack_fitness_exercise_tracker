.PHONY: up down build restart logs clean

# Docker Compose komutları
up:
	docker-compose up -d

down:
	docker-compose down

build:
	docker-compose build --no-cache

restart: down up

logs:
	docker-compose logs -f

# Veritabanını tamamen temizle (volume'ları da sil)
clean:
	docker-compose down -v
	docker volume prune -f

# Migration'ları manuel çalıştır
migrate:
	docker-compose exec php-fpm php bin/console doctrine:migrations:migrate

# Veritabanı durumunu kontrol et
db-status:
	docker-compose exec php-fpm php bin/console doctrine:migrations:status

# Container'lara bağlan
shell-php:
	docker-compose exec php-fpm sh

shell-db:
	docker-compose exec db mysql -u root -proot app 