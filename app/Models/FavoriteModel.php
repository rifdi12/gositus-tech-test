<?php

namespace App\Models;

use CodeIgniter\Model;

class FavoriteModel extends Model
{
    protected $table            = 'favorites';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'book_id'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer',
        'book_id' => 'required|integer'
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    public function getUserFavorites($userId)
    {
        return $this->select('favorites.*, books.title, books.description, books.image, books.created_at as book_created')
                    ->join('books', 'books.id = favorites.book_id')
                    ->where('favorites.user_id', $userId)
                    ->orderBy('favorites.created_at', 'DESC')
                    ->findAll();
    }

    public function isFavorite($userId, $bookId)
    {
        return $this->where('user_id', $userId)
                    ->where('book_id', $bookId)
                    ->first() !== null;
    }

    public function toggleFavorite($userId, $bookId)
    {
        $favorite = $this->where('user_id', $userId)
                         ->where('book_id', $bookId)
                         ->first();

        if ($favorite) {
            return $this->delete($favorite['id']);
        } else {
            return $this->insert([
                'user_id' => $userId,
                'book_id' => $bookId
            ]);
        }
    }
}