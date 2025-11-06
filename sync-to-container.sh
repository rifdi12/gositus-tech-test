#!/bin/bash
# Script untuk sync file ke container (workaround untuk macOS Docker volume sync issue)

echo "Syncing critical files to container..."

# Libraries
docker cp app/Libraries/QdrantService.php elibrary-app:/var/www/html/app/Libraries/QdrantService.php
docker cp app/Libraries/VectorStoreService.php elibrary-app:/var/www/html/app/Libraries/VectorStoreService.php
docker cp app/Libraries/PdfProcessorService.php elibrary-app:/var/www/html/app/Libraries/PdfProcessorService.php
docker cp app/Libraries/DeepSeekService.php elibrary-app:/var/www/html/app/Libraries/DeepSeekService.php

# Controllers
docker cp app/Controllers/Books.php elibrary-app:/var/www/html/app/Controllers/Books.php
docker cp app/Controllers/AiChat.php elibrary-app:/var/www/html/app/Controllers/AiChat.php

echo "✅ Files synced successfully!"
echo "Note: This is a temporary workaround. For production, use proper deployment."
