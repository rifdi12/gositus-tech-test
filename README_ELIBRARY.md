# E-Library Application

Aplikasi perpustakaan digital yang dibangun menggunakan CodeIgniter 4 dan Bootstrap 5.

## Fitur

### 1. Authentication System
- **Login**: Form login dengan email dan password
- **Register**: Form registrasi dengan validasi password yang kuat
- **Logout**: Fungsi keluar dari sistem

### 2. User Management
- **Role Admin**: Dapat mengelola buku (CRUD)
- **Role User**: Dapat melihat katalog dan menambah favorit

### 3. Navbar
- Menu Katalog, Favorit, Profil
- Field pencarian dengan icon search
- Tombol Upload (hanya admin)
- Tombol Keluar

### 4. Katalog Buku
- Menampilkan semua buku dalam grid layout
- Fitur pencarian berdasarkan judul atau deskripsi
- Tombol favorit pada setiap buku
- Responsive design dengan Bootstrap 5

### 5. Upload Content (Admin Only)
- Form upload dengan gambar, nama buku, dan deskripsi
- Validasi file gambar (JPG, PNG, GIF, max 2MB)
- Preview gambar sebelum upload
- Character counter untuk deskripsi

### 6. Favorit System
- Toggle favorit dengan AJAX
- Halaman khusus untuk melihat buku favorit
- Real-time update UI

### 7. Profil User
- Informasi akun
- Statistik (jumlah favorit, upload)
- Tanggal bergabung

## Akun Demo

### Administrator
- **Email**: admin@elibrary.com
- **Password**: Admin123
- **Akses**: Dapat melakukan CRUD buku, upload content

### User Biasa
- **Email**: user@elibrary.com  
- **Password**: User123
- **Akses**: Dapat melihat katalog dan mengelola favorit

## Instalasi dan Setup

### 1. Persiapan Environment
```bash
# Clone/Download project
cd /path/to/your/project

# Install dependencies
composer install

# Copy environment file
cp .env.example .env
```

### 2. Konfigurasi Database
```bash
# Buat database MySQL
CREATE DATABASE elibrary_db;

# Update konfigurasi di .env
database.default.hostname = localhost
database.default.database = elibrary_db
database.default.username = root
database.default.password = 
```

### 3. Migrasi Database
```bash
# Jalankan migrasi
php spark migrate

# Jalankan seeder untuk akun default
php spark db:seed UserSeeder
```

### 4. Set Permissions
```bash
# Buat folder uploads dan set permission
mkdir -p public/uploads/books
chmod 755 public/uploads/books
```

### 5. Jalankan Server
```bash
# Start development server
php spark serve

# Akses aplikasi di browser
http://localhost:8080
```

## Struktur Database

### Table: users
- id (INT, Primary Key)
- email (VARCHAR 255, Unique)
- password (VARCHAR 255)
- role (ENUM: 'admin', 'user')
- created_at, updated_at (DATETIME)

### Table: books
- id (INT, Primary Key)
- title (VARCHAR 255)
- description (TEXT)
- image (VARCHAR 255)
- uploaded_by (INT, Foreign Key to users.id)
- created_at, updated_at (DATETIME)

### Table: favorites
- id (INT, Primary Key)
- user_id (INT, Foreign Key to users.id)
- book_id (INT, Foreign Key to books.id)
- created_at (DATETIME)

## Validasi Password

Password harus memenuhi kriteria berikut:
- Minimal 8 karakter
- Mengandung huruf besar (A-Z)
- Mengandung huruf kecil (a-z)
- Mengandung angka (0-9)

## CSS Framework

Menggunakan **Bootstrap 5** dengan fitur:
- Responsive grid system
- Form validation styling
- Card components
- Button styling
- Modal components
- Icon integration (Bootstrap Icons)

## Keamanan

- CSRF Protection aktif
- Password hashing menggunakan PHP password_hash()
- Input validation dan sanitization
- File upload validation
- Role-based access control

## Browser Support

- Chrome 60+
- Firefox 60+
- Safari 12+
- Edge 79+

## Troubleshooting

### Error "Class Locale not found"
```bash
# Install PHP Intl extension
# macOS dengan Homebrew:
brew install php-intl

# Ubuntu/Debian:
sudo apt-get install php-intl

# Atau jalankan dengan ignore platform requirements:
composer install --ignore-platform-req=ext-intl
```

### Permission Denied pada uploads
```bash
chmod -R 755 public/uploads/
```

### Database Connection Error
- Pastikan MySQL service berjalan
- Cek konfigurasi database di .env
- Pastikan database sudah dibuat

## Fitur Tambahan

- Real-time character counter
- Image preview saat upload
- Password visibility toggle
- Loading states pada form
- Konfirmasi sebelum delete
- Flash messages untuk feedback
- Mobile-responsive design

## Contact

Untuk pertanyaan atau dukungan, silakan hubungi developer.