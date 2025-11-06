# Testing Guide - Local Development

## 🧪 Running Tests Locally

### Option 1: Run Tests in Docker (Recommended)

Karena aplikasi berjalan di Docker, cara terbaik adalah menjalankan test di dalam container:

```bash
# Install dependencies di container (hanya sekali)
docker-compose exec app composer install

# Jalankan semua tests
docker-compose exec app vendor/bin/phpunit --testdox

# Jalankan unit tests saja
docker-compose exec app vendor/bin/phpunit --testsuite unit --testdox

# Jalankan feature tests saja
docker-compose exec app vendor/bin/phpunit --testsuite feature --testdox

# Generate coverage report
docker-compose exec app vendor/bin/phpunit --coverage-text
```

### Option 2: Run Tests Locally (Membutuhkan MySQL Lokal)

Jika ingin menjalankan test di luar Docker, pastikan:

1. **Install MySQL** di sistem Anda
2. **Buat test database**:
   ```bash
   mysql -u root -p
   CREATE DATABASE elibrary_test;
   ```

3. **Update .env** untuk test database:
   ```bash
   cp env .env
   # Edit .env, uncomment dan set:
   # database.tests.hostname = localhost
   # database.tests.database = elibrary_test
   # database.tests.username = root
   # database.tests.password = your_password
   # database.tests.DBDriver = MySQLi
   ```

4. **Run tests**:
   ```bash
   composer install
   vendor/bin/phpunit --testdox
   ```

## 📊 Test Structure

```
tests/
├── unit/                      # Unit tests
│   ├── UserModelTest.php     # User model tests
│   ├── BookModelTest.php     # Book model tests
│   └── FavoriteModelTest.php # Favorite model tests
│
├── feature/                   # Feature/Integration tests
│   ├── AuthControllerTest.php
│   └── BooksControllerTest.php
│
└── _support/
    └── Database/
        └── Seeds/
            └── TestSeeder.php # Test data seeder
```

## 🎯 Test Coverage

Target coverage: **>80%**

Untuk melihat coverage report:
```bash
# Di Docker
docker-compose exec app vendor/bin/phpunit --coverage-html build/coverage

# Buka di browser
open build/coverage/index.html
```

## ✅ Quick Test Commands

### Makefile Commands (Jika tersedia)
```bash
make test              # Run all tests
make test-unit         # Unit tests only
make test-feature      # Feature tests only
make test-coverage     # With coverage report
make test-docker       # In Docker container
```

### Direct PHPUnit Commands
```bash
# Specific test file
docker-compose exec app vendor/bin/phpunit tests/unit/UserModelTest.php

# Specific test method
docker-compose exec app vendor/bin/phpunit --filter testCreateUser

# Stop on first failure
docker-compose exec app vendor/bin/phpunit --stop-on-failure

# Verbose output
docker-compose exec app vendor/bin/phpunit --testdox --verbose
```

## 🐛 Troubleshooting

### "No such file or directory" error untuk phpunit
```bash
# Install dependencies
docker-compose exec app composer install
```

### Database connection errors
```bash
# Pastikan MySQL container berjalan
docker-compose ps

# Restart database
docker-compose restart db

# Check logs
docker-compose logs db
```

### Permission errors untuk writable/
```bash
docker-compose exec app chmod -R 777 writable/
```

### Clear cache sebelum testing
```bash
docker-compose exec app php spark cache:clear
docker-compose exec app rm -rf writable/cache/*
```

## 📝 Writing New Tests

### Example Unit Test
```php
<?php
namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class MyTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $refresh     = true;

    public function testExample()
    {
        $this->assertTrue(true);
    }
}
```

### Example Feature Test
```php
<?php
namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class MyFeatureTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testEndpoint()
    {
        $result = $this->get('/some/endpoint');
        $result->assertStatus(200);
    }
}
```

## 🚀 CI/CD Testing

Tests akan otomatis berjalan di GitHub Actions untuk:
- ✅ Setiap push ke repository
- ✅ Setiap pull request
- ✅ Multiple PHP versions (8.1, 8.2, 8.3)

Lihat [TESTING.md](TESTING.md) untuk detail lengkap CI/CD pipeline.
