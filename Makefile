COMPOSE = docker compose -f compose.yml

.PHONY: help up down restart build ps logs app-logs shell artisan migrate fresh seed test pint install clean

help:
	@echo "Available commands:"
	@echo "  make up         - Start containers in background"
	@echo "  make down       - Stop containers"
	@echo "  make restart    - Restart full stack"
	@echo "  make build      - Build/rebuild images"
	@echo "  make ps         - Show container status"
	@echo "  make logs       - Tail all service logs"
	@echo "  make app-logs   - Tail app logs only"
	@echo "  make shell      - Open shell in app container"
	@echo "  make artisan c='about'         - Run artisan command"
	@echo "  make migrate    - Run database migrations"
	@echo "  make fresh      - Fresh migrate with seed"
	@echo "  make seed       - Run database seeder"
	@echo "  make test       - Run test suite"
	@echo "  make pint       - Run Laravel Pint formatter"
	@echo "  make install    - Install composer dependencies in app container"
	@echo "  make clean      - Stop stack and remove volumes"

up:
	$(COMPOSE) up --build -d

down:
	$(COMPOSE) down

restart: down up

build:
	$(COMPOSE) build

ps:
	$(COMPOSE) ps

logs:
	$(COMPOSE) logs -f

app-logs:
	$(COMPOSE) logs -f app

shell:
	$(COMPOSE) exec app sh

artisan:
	$(COMPOSE) exec app php artisan $(c)

migrate:
	$(COMPOSE) exec app php artisan migrate --force

fresh:
	$(COMPOSE) exec app php artisan migrate:fresh --seed --force

seed:
	$(COMPOSE) exec app php artisan db:seed --force

test:
	$(COMPOSE) exec app php artisan test --compact

pint:
	$(COMPOSE) exec app vendor/bin/pint --dirty --format=agent

install:
	$(COMPOSE) exec app composer install

clean:
	$(COMPOSE) down -v
