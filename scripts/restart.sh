#!/bin/bash

# Restart E-Library Docker containers

echo "🔄 Restarting E-Library containers..."
docker-compose restart

echo "✅ E-Library containers restarted successfully!"
echo "🌐 Application: http://localhost:8080"