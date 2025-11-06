<?php

namespace App\Controllers;

use App\Models\BookModel;
use App\Models\FavoriteModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class Dashboard extends BaseController
{
    protected $bookModel;
    protected $favoriteModel;
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->bookModel = new BookModel();
        $this->favoriteModel = new FavoriteModel();
        $this->userModel = new UserModel();
        $this->session = \Config\Services::session();
    }

    public function index()
    {
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/login');
        }

        $search = $this->request->getGet('search');
        $books = $this->bookModel->searchBooks($search);
        $userId = $this->session->get('user_id');

        // Add favorite status to each book
        foreach ($books as &$book) {
            $book['is_favorite'] = $this->favoriteModel->isFavorite($userId, $book['id']);
        }

        $data = [
            'title' => 'Katalog Buku - E-Library',
            'books' => $books,
            'search' => $search
        ];

        return view('dashboard/index', $data);
    }

    public function favorites()
    {
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/login');
        }

        $userId = $this->session->get('user_id');
        $favorites = $this->favoriteModel->getUserFavorites($userId);

        $data = [
            'title' => 'Buku Favorit - E-Library',
            'favorites' => $favorites
        ];

        return view('dashboard/favorites', $data);
    }

    public function profile()
    {
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/login');
        }

        $userId = $this->session->get('user_id');
        $user = $this->userModel->find($userId);
        
        // Count user statistics
        $totalFavorites = $this->favoriteModel->where('user_id', $userId)->countAllResults();
        $totalUploads = 0;
        
        if ($user['role'] === 'admin') {
            $totalUploads = $this->bookModel->where('uploaded_by', $userId)->countAllResults();
        }

        $data = [
            'title' => 'Profil - E-Library',
            'user' => $user,
            'totalFavorites' => $totalFavorites,
            'totalUploads' => $totalUploads
        ];

        return view('dashboard/profile', $data);
    }

    public function toggleFavorite()
    {
        if (!$this->session->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $json = $this->request->getJSON();
        $bookId = $json->book_id ?? null;
        $userId = $this->session->get('user_id');

        if (!$bookId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Book ID required']);
        }

        $result = $this->favoriteModel->toggleFavorite($userId, $bookId);
        $isFavorite = $this->favoriteModel->isFavorite($userId, $bookId);

        return $this->response->setJSON([
            'success' => $result,
            'is_favorite' => $isFavorite
        ]);
    }
}