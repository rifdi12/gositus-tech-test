<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

class BooksControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = 'App';
    protected $seed        = 'Tests\Support\Database\Seeds\TestSeeder';

    protected function setUp(): void
    {
        parent::setUp();
        // Set session as admin user for tests
        $_SESSION['user_id'] = 1;
        $_SESSION['email'] = 'admin@test.com';
        $_SESSION['role'] = 'admin';
    }

    public function testUploadPageDisplaysForAdmin()
    {
        $result = $this->get('/books/upload');
        
        $result->assertStatus(200);
        $result->assertSee('Upload Buku');
    }

    public function testUserCannotAccessUploadPage()
    {
        // Change session to regular user
        $_SESSION['role'] = 'user';

        $result = $this->get('/books/upload');
        
        $result->assertRedirectTo('/dashboard');
    }

    public function testSearchBooks()
    {
        $result = $this->get('/books/search?q=Test');
        
        $result->assertStatus(200);
        $result->assertSeeInJson(['success' => true]);
    }

    public function testToggleFavorite()
    {
        $result = $this->post('/books/favorite/1');
        
        $result->assertStatus(200);
        $result->assertSeeInJson(['success' => true]);
    }

    public function testGetBookDetails()
    {
        $result = $this->get('/books/detail/1');
        
        $result->assertStatus(200);
    }

    public function testDeleteBookAsAdmin()
    {
        $result = $this->post('/books/delete/1');
        
        $result->assertRedirectTo('/dashboard');
    }

    public function testUserCannotDeleteBook()
    {
        $_SESSION['role'] = 'user';

        $result = $this->post('/books/delete/1');
        
        $result->assertRedirectTo('/dashboard');
    }

    public function testEditPageDisplaysForAdmin()
    {
        $result = $this->get('/books/edit/1');
        
        $result->assertStatus(200);
        $result->assertSee('Edit Buku');
    }

    public function testUserCannotAccessEditPage()
    {
        $_SESSION['role'] = 'user';

        $result = $this->get('/books/edit/1');
        
        $result->assertRedirectTo('/dashboard');
    }
}
