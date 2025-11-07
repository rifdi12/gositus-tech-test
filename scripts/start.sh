#!/bin/bash

# Quick start script for E-Library Docker setup

echo "🚀 Starting E-Library Application with Docker..."
echo "================================================"

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker Desktop first."
    exit 1
fi

# Build and start containers
echo "📦 Building and starting containers..."
docker-compose up --build -d

# Wait a moment for services to be ready
echo "⏳ Waiting for services to start..."
sleep 10

# Fix cache directory permissions
echo "🔧 Fixing writable directory permissions..."
docker exec elibrary-app bash -c "mkdir -p /var/www/html/writable/cache && chmod -R 777 /var/www/html/writable"

# Run database migrations and seeding (only if needed)
echo "🗄️  Checking database status..."
if docker exec elibrary-app php spark migrate:status | grep -q "| 0"; then
    echo "📊 Running database migrations..."
    docker exec elibrary-app php spark migrate --all
    echo "🌱 Seeding database with demo data..."
    docker exec elibrary-app php spark db:seed UserSeeder
    echo "✅ Database initialized successfully!"
else
    echo "ℹ️  Database already initialized, skipping migration and seeding."
fi

# Show status
echo ""
echo "✅ E-Library is now running!"
echo "================================================"
echo "🌐 Application: http://localhost:8080"
echo "🗄️  phpMyAdmin: http://localhost:8081"
echo ""
echo "👤 Demo Accounts:"
echo "   Admin: admin@elibrary.com / Admin123"
echo "   User:  user@elibrary.com / User123"
echo ""
echo "🔧 Useful commands:"
echo "   Stop:    ./scripts/stop.sh"
echo "   Restart: ./scripts/restart.sh"
echo "   Logs:    docker-compose logs -f"
echo "   Shell:   docker exec -it elibrary-app bash"