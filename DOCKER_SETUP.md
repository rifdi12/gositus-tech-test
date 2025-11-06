# 🐳 E-Library Docker Setup

Aplikasi E-Library yang sudah dikemas menggunakan Docker untuk memudahkan deployment dan development.

## 🚀 Quick Start

### Prasyarat
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) harus terinstall dan berjalan
- Git (untuk clone repository)

### Langkah-langkah Setup

1. **Clone Repository**
   ```bash
   git clone <repository-url>
   cd gositus-home-test
   ```

2. **Jalankan Aplikasi (One Command!)**
   ```bash
   ./scripts/start.sh
   ```

3. **Akses Aplikasi**
   - **Aplikasi E-Library**: http://localhost:8080
   - **phpMyAdmin**: http://localhost:8081
   - **Database**: localhost:3306

### 🔐 Akun Demo

| Role  | Email                | Password |
|-------|---------------------|----------|
| Admin | admin@elibrary.com  | Admin123 |
| User  | user@elibrary.com   | User123  |

## 📦 Arsitektur Docker

### Services yang Berjalan

1. **app** (port 8080)
   - CodeIgniter 4 application
   - PHP 8.2 dengan Apache
   - Semua ekstensi PHP yang diperlukan (intl, pdo_mysql, gd, dll)

2. **db** (port 3306)
   - MySQL 8.0
   - Database: `elibrary_db`
   - Auto-setup dengan migrations dan seeders

3. **phpmyadmin** (port 8081)
   - Web interface untuk manage database
   - Login: root / root_password

## 🛠️ Perintah Docker

### Script Bantuan (Recommended)

```bash
# Start aplikasi
./scripts/start.sh

# Stop aplikasi
./scripts/stop.sh

# Restart aplikasi
./scripts/restart.sh

# Fresh install (hapus semua data)
./scripts/fresh-setup.sh

# Masuk ke container aplikasi
./scripts/shell.sh
```

### Perintah Docker Manual

```bash
# Build dan start semua services
docker-compose up --build -d

# Stop semua services
docker-compose down

# Lihat logs
docker-compose logs -f

# Masuk ke container aplikasi
docker exec -it elibrary-app bash

# Restart service tertentu
docker-compose restart app
```

## 🔧 Development

### File Structure Docker

```
├── Dockerfile                 # Container definition untuk aplikasi
├── docker-compose.yml         # Orchestration semua services
├── .env.docker               # Environment variables untuk Docker
├── docker/
│   ├── entrypoint.sh         # Script setup otomatis
│   └── mysql/
│       └── init.sql          # Database initialization
└── scripts/                  # Helper scripts
    ├── start.sh
    ├── stop.sh
    ├── restart.sh
    ├── fresh-setup.sh
    └── shell.sh
```

### Konfigurasi Environment

File `.env.docker` otomatis digunakan ketika aplikasi berjalan di Docker dengan konfigurasi:

- Database host: `db` (Docker service name)
- Database name: `elibrary_db`
- Database user: `elibrary_user`
- Auto-migration dan seeder pada startup

### Volume Mapping

- `./public/uploads` → Container uploads (persistent file storage)
- `./writable` → Container writable (logs, cache, sessions)
- Database data → Docker volume `db_data` (persistent database)

## 🐛 Troubleshooting

### Port Already in Use
```bash
# Jika port 8080 sudah digunakan, ubah di docker-compose.yml
ports:
  - "8081:80"  # Ubah 8080 ke port lain
```

### Database Connection Error
```bash
# Reset database
docker-compose down -v
docker-compose up --build -d
```

### Permission Issues
```bash
# Fix permissions
docker exec -it elibrary-app bash
chown -R www-data:www-data /var/www/html
chmod -R 777 /var/www/html/writable
chmod -R 777 /var/www/html/public/uploads
```

### View Logs
```bash
# Lihat logs semua services
docker-compose logs -f

# Lihat logs aplikasi saja
docker-compose logs -f app

# Lihat logs database saja
docker-compose logs -f db
```

## 📋 Checklist Deployment

- [ ] Docker Desktop berjalan
- [ ] Port 8080, 8081, 3306 tersedia
- [ ] Clone repository
- [ ] Jalankan `./scripts/start.sh`
- [ ] Akses http://localhost:8080
- [ ] Test login dengan akun demo
- [ ] Test upload file (admin)
- [ ] Test pencarian dan favorit

## 🚀 Production Ready

Untuk production, ubah konfigurasi berikut:

1. **Environment Variables**
   ```yaml
   environment:
     - CI_ENVIRONMENT=production
   ```

2. **Security**
   - Ganti password database default
   - Hapus phpMyAdmin dari production
   - Gunakan HTTPS

3. **Performance**
   - Enable opcache
   - Configure MySQL for production
   - Use Redis for sessions

## ❓ FAQ

**Q: Kenapa perlu Docker?**
A: Docker memastikan environment yang sama di semua mesin, tidak perlu setup PHP, MySQL, dll secara manual.

**Q: Bagaimana cara update aplikasi?**
A: Pull perubahan baru, lalu jalankan `./scripts/fresh-setup.sh`

**Q: Bisakah database data tetap ada setelah restart?**
A: Ya, database menggunakan Docker volume yang persistent.

**Q: Bagaimana cara backup database?**
A: 
```bash
docker exec elibrary-db mysqldump -u root -proot_password elibrary_db > backup.sql
```

**Q: Bagaimana cara restore database?**
A:
```bash
docker exec -i elibrary-db mysql -u root -proot_password elibrary_db < backup.sql
```

---

## 🎯 Benefits Docker Setup

✅ **Easy Setup**: Satu command untuk menjalankan aplikasi
✅ **Consistent Environment**: Sama di semua mesin
✅ **No Dependencies**: Tidak perlu install PHP, MySQL manual
✅ **Isolated**: Tidak bentrok dengan aplikasi lain
✅ **Scalable**: Mudah di-deploy ke server production
✅ **Complete Stack**: App + Database + Admin tools

**Happy Coding! 🚀**