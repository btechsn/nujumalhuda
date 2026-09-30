.PHONY: help up down restart build logs shell migrate seed test lint fix permissions

help: ## Affiche cette aide
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

up: ## Démarre les services Docker
	docker-compose up -d

down: ## Arrête les services Docker
	docker-compose down

restart: down up ## Redémarre les services

build: ## Reconstruit les images Docker
	docker-compose build --no-cache

logs: ## Affiche les logs (usage: make logs SERVICE=app)
	@if [ -z "$(SERVICE)" ]; then \
		docker-compose logs -f --tail=100; \
	else \
		docker-compose logs -f --tail=100 $(SERVICE); \
	fi

shell: ## Ouvre un shell dans le conteneur app
	docker-compose exec app sh

shell-next: ## Ouvre un shell dans le conteneur next
	docker-compose exec next sh

migrate: ## Exécute les migrations
	docker-compose exec app php artisan migrate

migrate-fresh: ## Réinitialise et relance les migrations
	docker-compose exec app php artisan migrate:fresh --seed

seed: ## Exécute les seeders
	docker-compose exec app php artisan db:seed

test: ## Lance les tests
	docker-compose exec app php artisan test

test-coverage: ## Lance les tests avec couverture
	docker-compose exec app php artisan test --coverage

lint: ## Vérifie le code (Pint + PHPStan)
	docker-compose exec app ./vendor/bin/pint --test
	docker-compose exec app ./vendor/bin/phpstan analyse

fix: ## Corrige automatiquement le code
	docker-compose exec app ./vendor/bin/pint

permissions: ## Corrige les permissions des fichiers
	docker-compose exec app chown -R www-data:www-data /var/www/backend/storage /var/www/backend/bootstrap/cache

install-backend: ## Installe les dépendances backend
	docker-compose exec app composer install

install-frontend: ## Installe les dépendances frontend
	docker-compose exec next npm install

key-generate: ## Génère la clé d'application Laravel
	docker-compose exec app php artisan key:generate

optimize: ## Optimise Laravel (cache config, routes, views)
	docker-compose exec app php artisan optimize

clear: ## Nettoie tous les caches
	docker-compose exec app php artisan optimize:clear

queue-work: ## Démarre un worker de queue (pour debug)
	docker-compose exec app php artisan queue:work --verbose

reverb-start: ## Démarre Reverb (pour debug)
	docker-compose exec app php artisan reverb:start --debug

prod-up: ## Démarre en mode production
	docker-compose -f docker-compose.yml -f compose.prod.yml up -d

prod-deploy: ## Déploiement complet en production
	git pull
	docker-compose -f docker-compose.yml -f compose.prod.yml build
	docker-compose -f docker-compose.yml -f compose.prod.yml up -d
	docker-compose exec app php artisan migrate --force
	docker-compose exec app php artisan optimize

backup-db: ## Sauvegarde la base de données
	docker-compose exec db pg_dump -U $(DB_USERNAME) $(DB_DATABASE) > backup_$(shell date +%Y%m%d_%H%M%S).sql

restore-db: ## Restaure la base de données (usage: make restore-db FILE=backup.sql)
	docker-compose exec -T db psql -U $(DB_USERNAME) $(DB_DATABASE) < $(FILE)

.DEFAULT_GOAL := help
