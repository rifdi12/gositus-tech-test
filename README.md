# 📚 E-Library Application

Aplikasi perpustakaan digital yang dibangun menggunakan **CodeIgniter 4** dan **Bootstrap 5**.

## 🚀 Quick Start dengan Docker (Recommended)

**Cara tercepat untuk menjalankan aplikasi:**

```bash
# Clone repository
git clone <repository-url>
cd gositus-home-test

# Jalankan dengan Docker (satu perintah!)
./scripts/start.sh
```

**Akses aplikasi:**
- 🌐 **Aplikasi**: http://localhost:8080
- 🗄️ **phpMyAdmin**: http://localhost:8081

**Demo accounts:**
- **Admin**: admin@elibrary.com / Admin123
- **User**: user@elibrary.com / User123

📖 **[Dokumentasi lengkap Docker →](DOCKER_SETUP.md)**

---

## 🧪 Testing & CI/CD

Aplikasi dilengkapi dengan **comprehensive test suite** dan **automated CI/CD pipeline**.

```bash
# Run all tests
make test

# Run unit tests only
make test-unit

# Run feature tests only
make test-feature

# Generate coverage report
make test-coverage
```

**Test Coverage:**
- ✅ Unit tests untuk Models (User, Book, Favorite)
- ✅ Feature tests untuk Controllers (Auth, Books, Dashboard)
- ✅ Integration tests untuk database dan authentication
- ✅ Validation tests untuk forms dan business logic

**CI/CD Pipeline:**
- ✅ Automated testing on every push/PR
- ✅ Multi-version PHP testing (8.1, 8.2, 8.3)
- ✅ Docker image build and push
- ✅ Automated deployment to production

📖 **[Dokumentasi lengkap Testing & CI/CD →](TESTING.md)**

---

## ✨ Fitur Aplikasi

### 🔐 Authentication System
- **Login**: Form login dengan email dan password
- **Register**: Form registrasi dengan validasi password yang kuat
- **Logout**: Fungsi keluar dari sistem
- **Password visibility toggle**: Show/hide password

### 👥 User Management
- **Role Admin**: Dapat mengelola buku (CRUD)
- **Role User**: Dapat melihat katalog dan menambah favorit

### 🧭 Navigation
- Navbar responsif dengan Bootstrap 5
- Menu: Katalog, Favorit, Profil
- Field pencarian dengan icon search
- Tombol Upload (khusus admin)
- Tombol Keluar

### 📖 Katalog Buku
- Grid layout dengan card design
- Fitur pencarian real-time
- Tombol favorit pada setiap buku
- Responsive untuk semua device

### 📤 Upload Content (Admin Only)
- Form upload: Gambar + Nama Buku + Deskripsi
- Preview gambar sebelum upload
- Validasi file gambar (JPG, PNG, GIF, max 2MB)
- Character counter untuk deskripsi
- Full CRUD operations

### ❤️ Sistem Favorit
- Toggle favorit dengan AJAX
- Halaman khusus buku favorit
- Real-time update UI

### 🤖 AI Features (NEW!)
- **PDF Upload**: Admin dapat upload e-book dalam format PDF
- **Vector Database**: Automatic extraction dan indexing PDF ke Qdrant
- **AI Chat**: User dapat bertanya tentang isi buku dengan bahasa natural
- **RAG (Retrieval-Augmented Generation)**: AI menjawab berdasarkan konten buku yang sebenarnya
- **Suggested Questions**: Pertanyaan yang disarankan untuk setiap buku
- **Real-time Chat Interface**: UI chat interaktif dengan typing indicators

📖 **[Dokumentasi lengkap AI Features →](AI_FEATURES.md)**

### 👤 Profil User
- Informasi akun lengkap
- Statistik (jumlah favorit, upload)
- Tanggal bergabung

## 🎨 Technology Stack

- **Backend**: CodeIgniter 4
- **Frontend**: Bootstrap 5 + Vanilla JavaScript
- **Database**: MySQL 8.0
- **Vector Database**: Qdrant
- **AI/LLM**: DeepSeek API
- **PDF Processing**: smalot/pdfparser
- **HTTP Client**: Guzzle
- **Icons**: Bootstrap Icons
- **Testing**: PHPUnit
- **Container**: Docker + Docker Compose
- **CI/CD**: GitHub Actions

## 📦 Deployment Options

### Option 1: Docker (Recommended) 🐳

Paling mudah dan konsisten:

```bash
./scripts/start.sh
```

**Benefits:**
- ✅ Setup dengan satu command
- ✅ Environment yang konsisten
- ✅ Tidak perlu install dependencies manual
- ✅ Isolated dan tidak bentrok
- ✅ Include database, Qdrant, dan admin tools

**Services yang berjalan:**
- 🌐 App (PHP 8.2 + Apache): http://localhost:8080
- 🗄️ MySQL 8.0: localhost:3306
- 🔍 phpMyAdmin: http://localhost:8081
- 🤖 Qdrant Vector DB: http://localhost:6333

### Option 2: Manual Setup

**Prerequisites:**
- PHP 8.1 or higher
- MySQL 8.0
- Composer
- Docker (for Qdrant)

```bash
# 1. Install dependencies
composer install

# 2. Setup environment
cp env .env
# Edit .env, set database credentials and DEEPSEEK_API_KEY

# 3. Setup database
php spark migrate
php spark db:seed InitialSeeder

# 4. Start Qdrant
docker run -p 6333:6333 -p 6334:6334 \
  -v $(pwd)/qdrant_data:/qdrant/storage \
  qdrant/qdrant

# 5. Start development server
php spark serve
```

## 🔧 Configuration

### Required Environment Variables

Edit file `env` atau `.env`:

```bash
# Database
database.default.hostname = db
database.default.database = elibrary
database.default.username = elibrary_user
database.default.password = elibrary_pass

# AI Services (REQUIRED untuk fitur AI)
DEEPSEEK_API_KEY = your_deepseek_api_key_here
QDRANT_HOST = qdrant
QDRANT_PORT = 6333
```

**Get DeepSeek API Key:**
1. Register at https://platform.deepseek.com/
2. Create API key in dashboard
3. Add to environment variables
4. Restart containers: `docker-compose restart`

## Setup

Copy `env` to `.env` and tailor for your app, specifically the baseURL
and any database settings.

## Important Change with index.php

`index.php` is no longer in the root of the project! It has been moved inside the *public* folder,
for better security and separation of components.

This means that you should configure your web server to "point" to your project's *public* folder, and
not to the project root. A better practice would be to configure a virtual host to point there. A poor practice would be to point your web server to the project root and expect to enter *public/...*, as the rest of your logic and the
framework are exposed.

**Please** read the user guide for a better explanation of how CI4 works!

## Repository Management

We use GitHub issues, in our main repository, to track **BUGS** and to track approved **DEVELOPMENT** work packages.
We use our [forum](http://forum.codeigniter.com) to provide SUPPORT and to discuss
FEATURE REQUESTS.

This repository is a "distribution" one, built by our release preparation script.
Problems with it can be raised on our forum, or as issues in the main repository.

## Server Requirements

PHP version 8.1 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

> [!WARNING]
> - The end of life date for PHP 7.4 was November 28, 2022.
> - The end of life date for PHP 8.0 was November 26, 2023.
> - If you are still using PHP 7.4 or 8.0, you should upgrade immediately.
> - The end of life date for PHP 8.1 will be December 31, 2025.

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library
