#!/bin/bash

# Entrypoint script for E-Library application

echo "Starting E-Library Application..."

# Copy environment file for Docker
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.docker /var/www/html/.env
    echo "Environment file configured for Docker"
fi

# Wait for database using PHP
echo "Waiting for database connection..."
max_attempts=30
attempt=0

while [ $attempt -lt $max_attempts ]; do
    if php -r "try { \$pdo = new PDO('mysql:host=db;dbname=elibrary_db', 'elibrary_user', 'elibrary_pass'); echo 'connected'; exit(0); } catch(Exception \$e) { exit(1); }" 2>/dev/null; then
        echo "Database is ready!"
        break
    fi
    attempt=$((attempt + 1))
    echo "Waiting for database... attempt $attempt/$max_attempts"
    sleep 2
done

if [ $attempt -eq $max_attempts ]; then
    echo "Warning: Could not connect to database after $max_attempts attempts"
    echo "Starting Apache anyway - migrations will run later"
else
    # Run database migrations
    echo "Running database migrations..."
    cd /var/www/html
    php spark migrate --all 2>&1 || echo "Migration might have already run"

    # Run database seeders
    echo "Running database seeders..."
    php spark db:seed UserSeeder 2>&1 || echo "Seeder might have already run"
fi

echo "E-Library setup completed!"
echo "========================================"
echo "Access the application at: http://localhost:8080"
echo "Admin login: admin@elibrary.com / Admin123"
echo "User login: user@elibrary.com / User123"
echo "phpMyAdmin: http://localhost:8081"
echo "========================================"

# Start Apache
exec "$@"