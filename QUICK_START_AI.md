# 🚀 Quick Start Guide - AI Features

Panduan singkat untuk mulai menggunakan fitur AI pada E-Library.

## Prerequisites

- Docker dan Docker Compose terinstall
- DeepSeek API Key (gratis di https://platform.deepseek.com/)

## Step 1: Setup Environment

Edit file `env` dan tambahkan DeepSeek API key:

```bash
DEEPSEEK_API_KEY=your_actual_api_key_here
```

Atau tambahkan langsung di `docker-compose.yml` di section `environment` untuk service `app`:

```yaml
services:
  app:
    environment:
      - DEEPSEEK_API_KEY=your_actual_api_key_here
      - QDRANT_HOST=qdrant
      - QDRANT_PORT=6333
```

## Step 2: Start Services

```bash
# Start semua containers
docker-compose up -d

# Verifikasi semua services running
docker-compose ps

# Cek logs jika ada masalah
docker-compose logs -f
```

Expected output:
```
NAME                IMAGE                  STATUS
elibrary-app        gositus-home-test-app  Up
elibrary-db         mysql:8.0              Up (healthy)
elibrary-phpmyadmin phpmyadmin             Up
elibrary-qdrant     qdrant/qdrant:latest   Up
```

## Step 3: Access Application

Buka browser:
- **App**: http://localhost:8080
- **Qdrant Dashboard**: http://localhost:6333/dashboard
- **phpMyAdmin**: http://localhost:8081

## Step 4: Upload PDF (Admin)

1. Login sebagai admin:
   - Email: `admin@elibrary.com`
   - Password: `Admin123`

2. Klik tombol **"Upload Buku"** di navbar

3. Isi form:
   - **Judul**: Masukkan judul buku
   - **Penulis**: Nama penulis
   - **Deskripsi**: Deskripsi singkat buku
   - **Gambar**: Upload cover buku (JPG/PNG, max 2MB)
   - **PDF File**: Upload file PDF (max 20MB)

4. Klik **"Upload"**

5. Tunggu proses:
   - Upload file (~1 detik)
   - Extract text dari PDF (~2-5 detik)
   - Create embeddings (~5-10 detik)
   - Store vectors di Qdrant (~1 detik)
   - **Total**: ~10-20 detik untuk buku berukuran medium

6. Verifikasi:
   - Lihat badge **"PDF"** dan **"AI"** di kartu buku
   - Cek logs: `docker-compose logs app | grep "PDF processed successfully"`

## Step 5: Test AI Chat (User)

1. Logout dari admin account

2. Login sebagai user:
   - Email: `user@elibrary.com`
   - Password: `User123`

3. Di dashboard, klik tombol **"Tanya AI"** pada buku yang sudah di-upload

4. Halaman detail buku akan terbuka dengan chat interface

5. Coba pertanyaan yang disarankan, atau ketik pertanyaan sendiri:
   - "Apa tema utama buku ini?"
   - "Siapa tokoh utama dalam buku ini?"
   - "Ringkas bab pertama"
   - "Apa yang dibahas di bab 3?"

6. AI akan menjawab berdasarkan isi buku dalam ~2-4 detik

## Troubleshooting

### Problem: "Qdrant connection failed"

```bash
# Cek status Qdrant
curl http://localhost:6333/collections

# Restart Qdrant
docker-compose restart qdrant

# Cek logs
docker-compose logs qdrant
```

### Problem: "DeepSeek API error: Unauthorized"

```bash
# Verifikasi API key
docker-compose exec app printenv | grep DEEPSEEK

# Update .env atau docker-compose.yml
# Kemudian restart
docker-compose down
docker-compose up -d
```

### Problem: "PDF processing failed"

```bash
# Cek logs untuk detail error
docker-compose logs app | grep -A 10 "PDF"

# Verifikasi file PDF valid:
# - Max 20MB
# - Format PDF yang benar (not corrupt)
# - Readable text (not scanned images)
```

### Problem: Chat tidak muncul / "Book not found"

```bash
# Cek database
docker-compose exec db mysql -u elibrary_user -pelibrary_pass elibrary \
  -e "SELECT id, title, pdf_file, has_vector FROM books;"

# Jika has_vector = 0, coba re-upload PDF atau trigger manual:
docker-compose exec app php spark migrate:refresh
```

## Verification Checklist

Gunakan checklist ini untuk memverifikasi setup:

- [ ] Docker containers running (4 containers)
- [ ] App accessible at http://localhost:8080
- [ ] Qdrant dashboard accessible at http://localhost:6333/dashboard
- [ ] `DEEPSEEK_API_KEY` environment variable set
- [ ] Database migration completed
- [ ] Admin login works
- [ ] PDF upload successful (file in `writable/uploads/pdfs/`)
- [ ] Badge "AI" appears on book card
- [ ] Qdrant collection created (check at http://localhost:6333/dashboard)
- [ ] User login works
- [ ] "Tanya AI" button visible
- [ ] Chat interface loads
- [ ] AI responds to questions

## Next Steps

1. **Read full documentation**: [AI_FEATURES.md](AI_FEATURES.md)
2. **Upload more books**: Try different PDF formats
3. **Test edge cases**: Very long questions, complex queries
4. **Monitor usage**: Check Qdrant storage and API usage
5. **Customize**: Adjust system prompts, chunk sizes, etc.

## Common Commands

```bash
# View logs
docker-compose logs -f app

# Restart specific service
docker-compose restart app

# Run migrations
docker-compose exec app php spark migrate

# Access MySQL
docker-compose exec db mysql -u elibrary_user -pelibrary_pass elibrary

# Clear logs
docker-compose exec app rm -rf writable/logs/*

# Backup database
docker-compose exec db mysqldump -u elibrary_user -pelibrary_pass elibrary > backup.sql

# Check Qdrant collections
curl http://localhost:6333/collections
```

## Performance Tips

1. **Optimize PDF size**:
   - Compress PDFs before upload
   - Use tools like `ghostscript` or online compressors
   - Recommended: < 5MB per file

2. **Batch processing**:
   - Upload multiple books at once
   - Processing happens sequentially

3. **Monitor resources**:
   ```bash
   # Check container resource usage
   docker stats
   ```

4. **Clean up old data**:
   ```bash
   # Remove unused Qdrant collections
   curl -X DELETE http://localhost:6333/collections/old_collection_name
   ```

## Demo Video (Optional)

If you recorded a demo:
1. Upload PDF buku sample
2. Wait for processing
3. Ask questions via chat
4. Show relevant answers

## Support

If you encounter issues:
1. Check logs: `docker-compose logs -f`
2. Verify environment variables
3. Test Qdrant connection: `curl http://localhost:6333/collections`
4. Review [AI_FEATURES.md](AI_FEATURES.md) documentation
5. Create issue with:
   - Error message
   - Steps to reproduce
   - Log snippets
   - Environment info

---

**Happy coding! 🚀**
