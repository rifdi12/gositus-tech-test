<?php

namespace Tests\Unit;

use App\Models\BookModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class BookModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = 'App';
    protected $seed        = 'Tests\Support\Database\Seeds\TestSeeder';

    protected BookModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new BookModel();
    }

    public function testCreateBook()
    {
        $data = [
            'title'       => 'Test Book',
            'description' => 'A test book description',
            'image'       => 'test-book.jpg',
            'uploaded_by' => 1,
        ];

        $id = $this->model->insert($data);
        $this->assertIsNumeric($id);

        $book = $this->model->find($id);
        $this->assertEquals('Test Book', $book['title']);
        $this->assertEquals('A test book description', $book['description']);
    }

    public function testGetAllBooks()
    {
        $books = $this->model->findAll();
        $this->assertIsArray($books);
    }

    public function testSearchBooks()
    {
        $data = [
            'title'       => 'Searchable Book',
            'description' => 'This is a searchable book',
            'image'       => 'search.jpg',
            'uploaded_by' => 1,
        ];

        $this->model->insert($data);
        
        $results = $this->model->like('title', 'Searchable')->findAll();
        $this->assertGreaterThan(0, count($results));
    }

    public function testUpdateBook()
    {
        $data = [
            'title'       => 'Original Title',
            'description' => 'Original description',
            'image'       => 'original.jpg',
            'uploaded_by' => 1,
        ];

        $id = $this->model->insert($data);
        
        $updateData = [
            'title'       => 'Updated Title',
            'description' => 'Updated description',
        ];

        $this->model->update($id, $updateData);
        $book = $this->model->find($id);

        $this->assertEquals('Updated Title', $book['title']);
        $this->assertEquals('Updated description', $book['description']);
    }

    public function testDeleteBook()
    {
        $data = [
            'title'       => 'Book to Delete',
            'description' => 'This book will be deleted',
            'image'       => 'delete.jpg',
            'uploaded_by' => 1,
        ];

        $id = $this->model->insert($data);
        $this->model->delete($id);
        
        $book = $this->model->find($id);
        $this->assertNull($book);
    }

    public function testGetBooksByUploader()
    {
        $userId = 1;
        
        $data = [
            'title'       => 'User Book',
            'description' => 'Book uploaded by user',
            'image'       => 'user-book.jpg',
            'uploaded_by' => $userId,
        ];

        $this->model->insert($data);
        
        $books = $this->model->where('uploaded_by', $userId)->findAll();
        $this->assertGreaterThan(0, count($books));
    }

    public function testBookValidation()
    {
        // Test missing required fields
        $data = [
            'title' => 'Incomplete Book',
            // Missing description, image, uploaded_by
        ];

        $result = $this->model->insert($data);
        $this->assertFalse($result);
    }

    public function testGetBookWithUploader()
    {
        $data = [
            'title'       => 'Book with Uploader',
            'description' => 'Testing join with users',
            'image'       => 'join-test.jpg',
            'uploaded_by' => 1,
        ];

        $id = $this->model->insert($data);
        
        $book = $this->model
            ->select('books.*, users.email as uploader_email')
            ->join('users', 'users.id = books.uploaded_by')
            ->find($id);

        $this->assertNotNull($book);
        $this->assertArrayHasKey('uploader_email', $book);
    }
}
