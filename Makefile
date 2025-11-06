# E-Library Docker Makefile

.PHONY: help start stop restart fresh logs shell build clean test test-unit test-feature test-coverage

# Default target
help:
	@echo "🐳 E-Library Docker Commands"
	@echo "=============================="
	@echo ""
	@echo "📋 Docker commands:"
	@echo "  make start      - Start all services"
	@echo "  make stop       - Stop all services"
	@echo "  make restart    - Restart all services"
	@echo "  make fresh      - Fresh setup (rebuild + clean data)"
	@echo "  make logs       - View logs (all services)"
	@echo "  make shell      - Enter application container"
	@echo "  make build      - Build containers only"
	@echo "  make clean      - Clean up containers and volumes"
	@echo ""
	@echo "🧪 Testing commands:"
	@echo "  make test          - Run all tests"
	@echo "  make test-unit     - Run unit tests only"
	@echo "  make test-feature  - Run feature tests only"
	@echo "  make test-coverage - Run tests with coverage"
	@echo ""
	@echo "🌐 Access URLs:"
	@echo "  App:        http://localhost:8080"
	@echo "  phpMyAdmin: http://localhost:8081"
	@echo ""
	@echo "👤 Demo accounts:"
	@echo "  Admin: admin@elibrary.com / Admin123"
	@echo "  User:  user@elibrary.com / User123"

start:
	@echo "🚀 Starting E-Library..."
	@docker-compose up -d
	@echo "✅ E-Library is now running at http://localhost:8080"

stop:
	@echo "🛑 Stopping E-Library..."
	@docker-compose down
	@echo "✅ E-Library stopped"

restart:
	@echo "🔄 Restarting E-Library..."
	@docker-compose restart
	@echo "✅ E-Library restarted"

fresh:
	@echo "🧹 Fresh setup - rebuilding everything..."
	@docker-compose down -v
	@docker system prune -f
	@docker-compose up --build -d
	@echo "✅ Fresh setup completed at http://localhost:8080"

logs:
	@echo "📋 Viewing logs (Ctrl+C to exit)..."
	@docker-compose logs -f

shell:
	@echo "🐚 Entering application container..."
	@docker exec -it elibrary-app bash

build:
	@echo "🏗️ Building containers..."
	@docker-compose build

clean:
	@echo "🧹 Cleaning up..."
	@docker-compose down -v
	@docker system prune -f
	@echo "✅ Cleanup completed"

# Show container status
status:
	@echo "📊 Container Status:"
	@docker-compose ps

# Testing commands
test:
	@echo "🧪 Running all tests..."
	@./scripts/test.sh all

test-unit:
	@echo "🧪 Running unit tests..."
	@./scripts/test.sh unit

test-feature:
	@echo "🧪 Running feature tests..."
	@./scripts/test.sh feature

test-coverage:
	@echo "🧪 Running tests with coverage..."
	@./scripts/test.sh coverage
	@echo "📊 Open build/coverage/index.html to view coverage report"

test-docker:
	@echo "🐳 Running tests in Docker..."
	@docker-compose exec app vendor/bin/phpunit --testdox