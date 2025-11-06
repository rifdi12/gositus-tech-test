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