#!/bin/bash

# Fresh setup - rebuild everything from scratch

echo "🧹 Cleaning up existing containers and data..."
docker-compose down -v
docker system prune -f

echo "🏗️  Building fresh containers..."
docker-compose up --build -d

echo "✅ Fresh setup completed!"
echo "🌐 Application: http://localhost:8080"