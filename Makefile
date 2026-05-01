.PHONY: dev dev-server dev-web build clean seed logs test-server test-web

dev:
	docker compose up --build

dev-infra:
	docker compose up postgres redis -d

dev-server:
	cd apps/server && go run cmd/main.go

dev-web:
	cd apps/web && npm run dev

build:
	docker compose build

clean:
	docker compose down -v
	docker system prune -f

seed:
	docker compose exec postgres psql -U cyberrange -d cyberrange -f /docker-entrypoint-initdb.d/init.sql

logs:
	docker compose logs -f

test-server:
	cd apps/server && go test ./...

test-web:
	cd apps/web && npm test
