# AI Features - Troubleshooting

## Issue: Fitur AI Tidak Langsung Aktif Setelah Upload PDF

### Penyebab:
1. **Database update error**: Error "There is no data to update" saat menyimpan status processing
2. **Volume sync issue di macOS**: File yang diedit tidak langsung tersinkronisasi ke container

### Solusi yang Sudah Diterapkan:

#### 1. Fix Database Update (Books.php)
Menggunakan `save()` dengan `id` instead of `update()`:
```php
$updateData = [
    'id' => $bookId,
    'has_vector' => 1,
    'collection_name' => $result['collection_name'],
    'total_pages' => $result['pages'],
    'processed_at' => date('Y-m-d H:i:s'),
];
$this->bookModel->save($updateData);
```

#### 2. Workaround untuk macOS Docker Volume Sync
Gunakan script `sync-to-container.sh`:
```bash
./sync-to-container.sh
```

Script ini akan copy file-file penting ke container:
- Libraries (Qdrant, VectorStore, PdfProcessor, DeepSeek)
- Controllers (Books, AiChat)

### Cara Test:
1. Upload buku baru dengan PDF
2. Check database:
   ```bash
   docker-compose exec db mysql -u root -proot_password elibrary_db \
     -e "SELECT id, title, pdf_file, has_vector, collection_name FROM books ORDER BY id DESC LIMIT 1;"
   ```
3. Check logs:
   ```bash
   docker-compose logs -f app | grep -i "pdf\|processing"
   ```

### Expected Behavior:
- PDF uploaded ✅
- `pdf_file` column terisi dengan nama file ✅
- Processing dimulai otomatis ✅
- Setelah 5-30 detik (tergantung ukuran PDF):
  - `has_vector` = 1 ✅
  - `collection_name` = book_{id} ✅
  - Badge "AI Ready" muncul di detail page ✅
  - Button "Tanya AI" aktif ✅

### Known Limitations:
- Hash-based embedding (development only)
  - Untuk production gunakan OpenAI ada-002 atau Sentence Transformers
- Volume sync delay di macOS
  - Gunakan `sync-to-container.sh` after editing files
  - Atau restart container: `docker-compose restart app`

### Alternative: Force Sync
Jika masih ada issue, restart container:
```bash
docker-compose restart app
```

## Issue: PDF Kosong / Tidak Tampil

### Penyebab:
Column `pdf_file` di database NULL meskipun file ter-upload

### Solusi:
Sudah ditambahkan logging untuk debug:
```php
log_message('info', "PDF uploaded: {$pdfFileName}");
log_message('info', "Inserting book with data: " . json_encode($data));
```

Check logs untuk melihat apakah PDF benar-benar di-save:
```bash
docker-compose exec app tail -f /var/www/html/writable/logs/log-$(date +%Y-%m-%d).log
```

### Manual Check:
1. List uploaded PDFs:
   ```bash
   docker-compose exec app ls -la /var/www/html/public/uploads/pdfs/
   ```
2. Check database:
   ```bash
   docker-compose exec db mysql -u root -proot_password elibrary_db \
     -e "SELECT id, title, pdf_file FROM books WHERE pdf_file IS NOT NULL;"
   ```

## Status: ✅ FIXED
- Search method sudah berfungsi
- AI Chat return proper responses
- Database update menggunakan save() instead of update()
- Logging ditambahkan untuk debugging
