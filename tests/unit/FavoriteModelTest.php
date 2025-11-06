<?php

namespace Tests\Unit;

use App\Models\FavoriteModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class FavoriteModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = 'App';

    protected FavoriteModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new FavoriteModel();
    }

    public function testAddFavorite()
    {
        $data = [
            'user_id' => 1,
            'book_id' => 1,
        ];

        $id = $this->model->insert($data);
        $this->assertIsNumeric($id);

        $favorite = $this->model->find($id);
        $this->assertEquals(1, $favorite['user_id']);
        $this->assertEquals(1, $favorite['book_id']);
    }

    public function testGetUserFavorites()
    {
        $userId = 1;
        
        $data = [
            ['user_id' => $userId, 'book_id' => 1],
            ['user_id' => $userId, 'book_id' => 2],
        ];

        foreach ($data as $favorite) {
            $this->model->insert($favorite);
        }

        $favorites = $this->model->where('user_id', $userId)->findAll();
        $this->assertGreaterThanOrEqual(2, count($favorites));
    }

    public function testRemoveFavorite()
    {
        $data = [
            'user_id' => 1,
            'book_id' => 1,
        ];

        $id = $this->model->insert($data);
        $this->model->delete($id);
        
        $favorite = $this->model->find($id);
        $this->assertNull($favorite);
    }

    public function testCheckIfFavoriteExists()
    {
        $data = [
            'user_id' => 1,
            'book_id' => 1,
        ];

        $this->model->insert($data);
        
        $exists = $this->model
            ->where('user_id', 1)
            ->where('book_id', 1)
            ->first();

        $this->assertNotNull($exists);
    }

    public function testToggleFavorite()
    {
        $userId = 1;
        $bookId = 1;

        // Add favorite
        $favorite = $this->model
            ->where('user_id', $userId)
            ->where('book_id', $bookId)
            ->first();

        if ($favorite) {
            $this->model->delete($favorite['id']);
            $exists = false;
        } else {
            $this->model->insert(['user_id' => $userId, 'book_id' => $bookId]);
            $exists = true;
        }

        $this->assertTrue($exists);

        // Toggle again (remove)
        $favorite = $this->model
            ->where('user_id', $userId)
            ->where('book_id', $bookId)
            ->first();

        if ($favorite) {
            $this->model->delete($favorite['id']);
            $exists = false;
        }

        $this->assertFalse($exists);
    }

    public function testGetFavoritesWithBookDetails()
    {
        $data = [
            'user_id' => 1,
            'book_id' => 1,
        ];

        $this->model->insert($data);
        
        $favorites = $this->model
            ->select('favorites.*, books.title, books.description, books.image')
            ->join('books', 'books.id = favorites.book_id')
            ->where('favorites.user_id', 1)
            ->findAll();

        $this->assertGreaterThan(0, count($favorites));
        if (count($favorites) > 0) {
            $this->assertArrayHasKey('title', $favorites[0]);
        }
    }

    public function testCountUserFavorites()
    {
        $userId = 1;
        
        $data = [
            ['user_id' => $userId, 'book_id' => 1],
            ['user_id' => $userId, 'book_id' => 2],
            ['user_id' => $userId, 'book_id' => 3],
        ];

        foreach ($data as $favorite) {
            $this->model->insert($favorite);
        }

        $count = $this->model->where('user_id', $userId)->countAllResults();
        $this->assertGreaterThanOrEqual(3, $count);
    }

    public function testUniqueFavorite()
    {
        $data = [
            'user_id' => 1,
            'book_id' => 1,
        ];

        $this->model->insert($data);
        
        // Try to insert duplicate
        $result = $this->model->insert($data);
        $this->assertFalse($result);
    }
}
