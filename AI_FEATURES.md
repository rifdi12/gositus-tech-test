# AI Features Documentation

## Overview

Aplikasi E-Library ini dilengkapi dengan fitur AI yang memungkinkan pengguna untuk bertanya tentang konten buku dalam bahasa natural. Sistem menggunakan teknologi RAG (Retrieval-Augmented Generation) untuk memberikan jawaban yang akurat berdasarkan isi PDF buku.

## Teknologi yang Digunakan

- **Qdrant**: Vector database untuk menyimpan embeddings dari chunks teks PDF
- **DeepSeek**: Large Language Model untuk menghasilkan jawaban natural language
- **PDF Parser**: Ekstraksi teks dari file PDF
- **Guzzle**: HTTP client untuk komunikasi dengan API

## Arsitektur Sistem

```
PDF Upload → Text Extraction → Text Chunking → Vector Embedding → Qdrant Storage
                                                                         ↓
User Question → Vector Search → Retrieve Relevant Chunks → DeepSeek → Answer
```

### RAG (Retrieval-Augmented Generation) Pattern

1. **Indexing Phase** (saat upload PDF):
   - Admin mengupload PDF
   - Sistem mengekstrak teks dari PDF
   - Teks dipecah menjadi chunks (1000 karakter dengan overlap 200 karakter)
   - Setiap chunk diubah menjadi vector embedding
   - Vector disimpan di Qdrant dengan metadata (page, book_id)

2. **Query Phase** (saat user bertanya):
   - User mengirim pertanyaan tentang buku
   - Pertanyaan diubah menjadi vector
   - Sistem mencari chunks yang paling relevan di Qdrant
   - Chunks relevan dikirim sebagai context ke DeepSeek
   - DeepSeek menghasilkan jawaban berdasarkan context

## Komponen Sistem

### 1. PdfProcessorService (`app/Libraries/PdfProcessorService.php`)

**Fungsi**: Ekstraksi dan preprocessing teks dari PDF

**Metode Utama**:
- `extractText($pdfPath)`: Extract seluruh teks dari PDF
- `splitIntoChunks($text, $chunkSize, $overlap)`: Memecah teks menjadi chunks
- `cleanText($text)`: Normalisasi whitespace dan karakter

**Konfigurasi**:
- Chunk size: 1000 karakter
- Overlap: 200 karakter (untuk menjaga konteks antar chunks)

### 2. QdrantService (`app/Libraries/QdrantService.php`)

**Fungsi**: Interface dengan Qdrant vector database

**Metode Utama**:
- `createCollection($collectionName, $vectorSize)`: Membuat collection baru
- `insertVectors($collectionName, $vectors)`: Insert batch vectors
- `search($collectionName, $queryVector, $limit, $filter)`: Semantic search
- `deleteCollection($collectionName)`: Hapus collection

**Konfigurasi**:
- Host: `QDRANT_HOST` (default: qdrant)
- Port: `QDRANT_PORT` (default: 6333)
- Distance metric: Cosine similarity
- Vector size: 384 (sesuaikan dengan model embedding)

**⚠️ PENTING**: Saat ini menggunakan `createSimpleEmbedding()` placeholder. Untuk production, gunakan model embedding yang proper seperti:
- OpenAI ada-002
- sentence-transformers (all-MiniLM-L6-v2)
- multilingual-e5-base (untuk bahasa Indonesia)

### 3. DeepSeekService (`app/Libraries/DeepSeekService.php`)

**Fungsi**: Generate jawaban menggunakan DeepSeek LLM

**Metode Utama**:
- `generateWithContext($question, $context)`: Generate jawaban dengan RAG
- `buildSystemPrompt()`: System prompt untuk behavior AI
- `buildContextPrompt($chunks)`: Format chunks sebagai context

**Konfigurasi**:
- API Key: `DEEPSEEK_API_KEY` (wajib diisi)
- Model: deepseek-chat (default)
- Max tokens: 1000
- Temperature: 0.7

**System Prompt**:
```
Anda adalah asisten AI yang membantu pengguna memahami isi buku.
Jawab pertanyaan berdasarkan konteks yang diberikan.
Jika informasi tidak ada dalam konteks, katakan dengan jujur.
Jawab dalam bahasa Indonesia dengan ramah dan jelas.
```

### 4. VectorStoreService (`app/Libraries/VectorStoreService.php`)

**Fungsi**: Orchestration layer untuk koordinasi semua service

**Metode Utama**:
- `processAndStoreBook($bookId, $pdfPath)`: Full pipeline PDF → vectors
- `queryBook($bookId, $question, $limit)`: RAG query
- `deleteBookVectors($collectionName)`: Cleanup

**Workflow `processAndStoreBook()`**:
```
1. Extract text dari PDF
2. Split menjadi chunks
3. Generate embeddings untuk setiap chunk
4. Create Qdrant collection
5. Insert vectors ke Qdrant
6. Update database (has_vector, collection_name, total_pages)
7. Log completion
```

### 5. AiChat Controller (`app/Controllers/AiChat.php`)

**Endpoints**:

#### POST `/ai/chat`
Mengirim pertanyaan dan mendapat jawaban dari AI.

**Request Body**:
```json
{
  "book_id": 1,
  "question": "Apa tema utama buku ini?"
}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "answer": "Tema utama buku ini adalah...",
    "chunks_used": 3
  }
}
```

**Authorization**: Memerlukan login

#### GET `/ai/book/:id`
Mendapatkan informasi status AI untuk buku.

**Response**:
```json
{
  "success": true,
  "data": {
    "book_id": 1,
    "title": "Judul Buku",
    "has_vector": true,
    "pdf_available": true,
    "total_pages": 250
  }
}
```

#### GET `/ai/suggestions/:id`
Mendapatkan pertanyaan yang disarankan.

**Response**:
```json
{
  "success": true,
  "data": [
    "Apa tema utama buku ini?",
    "Siapa tokoh utama dalam buku ini?",
    "Apa kesimpulan dari buku ini?"
  ]
}
```

#### GET `/ai/status`
Health check dan statistik (admin only).

**Response**:
```json
{
  "success": true,
  "data": {
    "qdrant_connected": true,
    "deepseek_configured": true,
    "total_collections": 5,
    "total_books_with_vectors": 5
  }
}
```

## Setup dan Konfigurasi

### 1. Environment Variables

Edit file `env` atau set environment variables di docker-compose.yml:

```bash
# Wajib
DEEPSEEK_API_KEY=your_deepseek_api_key

# Optional (sudah di-set via docker-compose.yml)
QDRANT_HOST=qdrant
QDRANT_PORT=6333
```

### 2. Mendapatkan DeepSeek API Key

1. Daftar di https://platform.deepseek.com/
2. Buat API key di dashboard
3. Copy API key dan tambahkan ke environment variables

### 3. Menjalankan Services

```bash
# Start semua containers
docker-compose up -d

# Cek logs
docker-compose logs -f app

# Cek status Qdrant
curl http://localhost:6333/collections
```

### 4. Akses Qdrant Dashboard

Buka browser: http://localhost:6333/dashboard

## Cara Penggunaan

### Upload PDF (Admin)

1. Login sebagai admin
2. Klik "Upload Buku"
3. Isi form:
   - Judul buku
   - Penulis
   - Deskripsi
   - Upload gambar cover
   - Upload file PDF (max 20MB)
4. Klik "Upload"

**Proses di Backend**:
- File PDF disimpan di `writable/uploads/pdfs/`
- Sistem mengekstrak teks dari PDF
- Teks dipecah menjadi chunks
- Chunks diubah menjadi vectors dan disimpan di Qdrant
- Database di-update dengan status `has_vector=1`

### Tanya AI (User)

1. Login sebagai user biasa atau admin
2. Di dashboard, klik tombol "Tanya AI" pada kartu buku
3. Halaman detail buku akan terbuka dengan chat interface
4. Klik salah satu pertanyaan yang disarankan, atau ketik pertanyaan sendiri
5. AI akan menjawab berdasarkan isi buku

**Tips Bertanya**:
- Tanya spesifik: "Apa definisi X di bab 3?"
- Minta ringkasan: "Ringkas bab 2 dalam 3 poin"
- Cari informasi: "Kapan peristiwa Y terjadi?"
- Minta penjelasan: "Jelaskan konsep Z dengan contoh"

## Database Schema

### Kolom Tambahan di Tabel `books`

```sql
ALTER TABLE books ADD COLUMN pdf_file VARCHAR(255) DEFAULT NULL;
ALTER TABLE books ADD COLUMN has_vector BOOLEAN DEFAULT FALSE;
ALTER TABLE books ADD COLUMN collection_name VARCHAR(100) DEFAULT NULL;
ALTER TABLE books ADD COLUMN total_pages INT DEFAULT NULL;
ALTER TABLE books ADD COLUMN processed_at DATETIME DEFAULT NULL;
```

**Penjelasan**:
- `pdf_file`: Path ke file PDF yang diupload
- `has_vector`: Status apakah PDF sudah diproses menjadi vectors
- `collection_name`: Nama collection di Qdrant
- `total_pages`: Jumlah halaman PDF
- `processed_at`: Timestamp kapan PDF selesai diproses

## Monitoring dan Troubleshooting

### Melihat Logs

```bash
# Application logs
docker-compose logs -f app

# Qdrant logs
docker-compose logs -f qdrant

# Semua logs
docker-compose logs -f
```

### Common Issues

#### 1. "Qdrant connection failed"

**Penyebab**: Qdrant container belum ready atau tidak bisa diakses

**Solusi**:
```bash
# Cek status container
docker-compose ps

# Restart Qdrant
docker-compose restart qdrant

# Cek koneksi
curl http://localhost:6333/collections
```

#### 2. "DeepSeek API error: Unauthorized"

**Penyebab**: API key tidak valid atau belum di-set

**Solusi**:
1. Cek `DEEPSEEK_API_KEY` di environment variables
2. Pastikan API key valid dan aktif
3. Restart container setelah update env:
   ```bash
   docker-compose down
   docker-compose up -d
   ```

#### 3. "PDF processing failed"

**Penyebab**: PDF corrupt, terlalu besar, atau format tidak didukung

**Solusi**:
- Pastikan file adalah PDF valid
- Maksimal ukuran 20MB
- Coba convert PDF dengan tools lain jika ada masalah encoding
- Cek logs untuk error detail:
  ```bash
  docker-compose logs app | grep "PDF"
  ```

#### 4. "No relevant information found"

**Penyebab**: Pertanyaan terlalu spesifik atau informasi tidak ada di buku

**Solusi**:
- Coba pertanyaan yang lebih umum
- Cek apakah buku sudah di-proses (badge "AI" muncul)
- Verifikasi di Qdrant dashboard apakah vectors sudah tersimpan

### Cek Status AI Features

```bash
# Login sebagai admin, kemudian akses:
curl -X GET http://localhost/ai/status \
  -H "Cookie: ci_session=your_session_cookie"
```

Response akan menunjukkan:
- Status koneksi Qdrant
- Status konfigurasi DeepSeek
- Jumlah collections
- Jumlah buku dengan vectors

## Performance Considerations

### 1. PDF Size

- **Recommended**: < 5MB, < 200 halaman
- **Maximum**: 20MB (hard limit di upload form)
- **Large PDFs**: Processing time bisa 30-60 detik

### 2. Query Latency

Typical latency untuk query:
- Vector search di Qdrant: 10-50ms
- DeepSeek API call: 1-3 detik
- Total: ~2-4 detik per query

### 3. Rate Limiting

**DeepSeek API**:
- Free tier: biasanya 100-500 requests/day
- Cek quota di dashboard DeepSeek

**Saran**:
- Implement caching untuk pertanyaan yang sering ditanya
- Add rate limiting per user
- Monitor API usage

### 4. Storage

**Qdrant**:
- 1 buku (~200 halaman) = ~200 chunks
- 1 chunk = 384 dimensions × 4 bytes = ~1.5KB per vector
- Total per buku: ~300KB
- 100 buku: ~30MB di Qdrant

**Database**:
- Metadata minimal, tidak ada impact signifikan

## Future Improvements

### 1. Proper Embeddings

Ganti `createSimpleEmbedding()` dengan model yang proper:

```php
// Option 1: OpenAI Embeddings
public function createEmbedding($text)
{
    $response = $this->httpClient->post('https://api.openai.com/v1/embeddings', [
        'json' => [
            'input' => $text,
            'model' => 'text-embedding-ada-002'
        ],
        'headers' => [
            'Authorization' => 'Bearer ' . getenv('OPENAI_API_KEY')
        ]
    ]);
    
    $data = json_decode($response->getBody(), true);
    return $data['data'][0]['embedding'];
}

// Option 2: Local Sentence Transformers (via Python API)
// Lebih murah, tapi perlu setup Python service
```

### 2. Async PDF Processing

Implement job queue untuk processing PDF di background:

```php
// Install: composer require codeigniter4/queue
use CodeIgniter\Queue\Queue;

public function store()
{
    // ... save book ...
    
    // Queue PDF processing
    Queue::push('ProcessPdfJob', [
        'book_id' => $bookId,
        'pdf_path' => $pdfPath
    ]);
    
    return redirect()->to('/books')->with('message', 
        'Buku berhasil diupload. Proses AI sedang berjalan di background.');
}
```

### 3. Conversation History

Simpan riwayat percakapan untuk context yang lebih baik:

```sql
CREATE TABLE chat_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (book_id) REFERENCES books(id)
);
```

### 4. AI Usage Analytics

Track penggunaan untuk monitoring:

```sql
CREATE TABLE ai_usage_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    question TEXT NOT NULL,
    response_time_ms INT,
    chunks_used INT,
    tokens_used INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### 5. Multi-language Support

Tambah support untuk bahasa lain:

```php
// Detect language dari pertanyaan
public function detectLanguage($text)
{
    // Use language detection library
}

// Adjust system prompt based on language
public function buildSystemPrompt($language = 'id')
{
    $prompts = [
        'id' => 'Anda adalah asisten AI...',
        'en' => 'You are an AI assistant...',
    ];
    return $prompts[$language];
}
```

### 6. Hybrid Search

Kombinasi vector search dengan keyword search:

```php
// Vector search + BM25
public function hybridSearch($collectionName, $question, $limit = 5)
{
    // Vector search
    $vectorResults = $this->qdrantService->search(
        $collectionName, 
        $this->createEmbedding($question), 
        $limit
    );
    
    // Keyword search (full-text)
    $keywordResults = $this->searchKeywords($collectionName, $question, $limit);
    
    // Merge and re-rank results
    return $this->rerank($vectorResults, $keywordResults);
}
```

### 7. Export Chat

Allow users to export chat history:

```php
public function exportChat($bookId)
{
    $messages = $this->chatModel->where([
        'user_id' => session()->get('id'),
        'book_id' => $bookId
    ])->findAll();
    
    // Generate PDF or DOCX
    return $this->generatePdf($messages);
}
```

## Testing

### Manual Testing

1. **Upload PDF**:
   ```bash
   # Login sebagai admin
   # Upload PDF via UI
   # Check logs
   docker-compose logs app | grep "PDF processed"
   ```

2. **Check Qdrant**:
   ```bash
   # List collections
   curl http://localhost:6333/collections
   
   # Get collection info
   curl http://localhost:6333/collections/book_1
   ```

3. **Test Chat**:
   ```bash
   # Via browser atau curl
   curl -X POST http://localhost/ai/chat \
     -H "Content-Type: application/json" \
     -H "Cookie: ci_session=your_session" \
     -d '{"book_id": 1, "question": "Apa tema utama buku ini?"}'
   ```

### Unit Tests (Future)

```php
// tests/unit/Services/PdfProcessorTest.php
public function testExtractText()
{
    $service = new PdfProcessorService();
    $text = $service->extractText(TESTPATH . 'fixtures/sample.pdf');
    $this->assertNotEmpty($text);
}

// tests/unit/Services/QdrantTest.php
public function testCreateCollection()
{
    $service = new QdrantService();
    $result = $service->createCollection('test_collection', 384);
    $this->assertTrue($result);
}
```

## Security Considerations

### 1. API Key Protection

- ✅ Jangan commit API key ke git
- ✅ Gunakan environment variables
- ✅ Rotate API key secara berkala

### 2. File Upload Validation

- ✅ Validate file extension (.pdf only)
- ✅ Check file size (max 20MB)
- ✅ Scan for malware (future improvement)

### 3. Rate Limiting

Implement rate limiting untuk mencegah abuse:

```php
// app/Filters/RateLimiter.php
public function before(RequestInterface $request, $arguments = null)
{
    $key = 'ratelimit:' . session()->get('id');
    $limit = 10; // 10 requests
    $period = 60; // per 60 seconds
    
    // Check cache
    $count = cache()->get($key) ?? 0;
    if ($count >= $limit) {
        return Services::response()
            ->setStatusCode(429)
            ->setJSON(['error' => 'Too many requests']);
    }
    
    cache()->save($key, $count + 1, $period);
}
```

### 4. Input Sanitization

- ✅ Sanitize user questions
- ✅ Limit question length (max 500 chars)
- ✅ Block malicious prompts (prompt injection)

## Support

Untuk pertanyaan atau issues:
1. Check logs first: `docker-compose logs -f`
2. Verify configuration: `curl http://localhost/ai/status`
3. Contact developer dengan informasi:
   - Error message dari logs
   - Steps to reproduce
   - Environment (Docker version, OS, etc.)

## License

Sama dengan license utama aplikasi.
