<?php

namespace App\Controllers;

use App\Models\BookModel;
use App\Libraries\VectorStoreService;

class AiChat extends BaseController
{
    protected BookModel $bookModel;
    protected VectorStoreService $vectorStore;
    protected $session;

    public function __construct()
    {
        $this->bookModel = new BookModel();
        $this->vectorStore = new VectorStoreService();
        $this->session = \Config\Services::session();
    }

    /**
     * Check if user is logged in
     */
    private function checkAuth()
    {
        if (!$this->session->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Anda harus login terlebih dahulu',
            ])->setStatusCode(401);
        }
        return null;
    }

    /**
     * Chat with AI about a book
     * POST /ai/chat
     */
    public function chat()
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $bookId = $this->request->getPost('book_id');
        $question = $this->request->getPost('question');

        // Validation
        if (empty($bookId) || empty($question)) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Book ID dan pertanyaan harus diisi',
            ])->setStatusCode(400);
        }

        // Check if book exists
        $book = $this->bookModel->find($bookId);
        if (!$book) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Buku tidak ditemukan',
            ])->setStatusCode(404);
        }

        // Check if book has PDF and vector data
        if (!$book['has_vector']) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Buku ini belum memiliki file PDF atau sedang dalam proses indexing',
                'answer' => 'Maaf, buku ini belum dapat digunakan untuk fitur AI. Admin perlu mengupload file PDF terlebih dahulu.',
            ])->setStatusCode(400);
        }

        // Query the book using AI with RAG
        try {
            $result = $this->vectorStore->queryBook(
                $bookId,
                $question,
                5 // Top 5 relevant chunks
            );

            // Log the query
            log_message('info', "AI Chat - User: {$this->session->get('user_id')}, Book: {$bookId}, Question: {$question}");

            return $this->response->setJSON([
                'success' => $result['success'],
                'answer' => $result['answer'] ?? '',
                'book' => [
                    'id' => $book['id'],
                    'title' => $book['title'],
                ],
                'context_used' => $result['context_used'] ?? 0,
                'error' => $result['error'] ?? null,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'AI Chat error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Terjadi kesalahan saat memproses pertanyaan',
                'answer' => 'Maaf, terjadi kesalahan sistem. Silakan coba lagi nanti.',
            ])->setStatusCode(500);
        }
    }

    /**
     * Get book details including AI availability
     * GET /ai/book/{id}
     */
    public function bookInfo($id)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $book = $this->bookModel->find($id);
        
        if (!$book) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Buku tidak ditemukan',
            ])->setStatusCode(404);
        }

        // Get vector stats if available
        $vectorStats = null;
        if ($book['has_vector']) {
            $vectorStats = $this->vectorStore->getBookStats($id);
        }

        return $this->response->setJSON([
            'success' => true,
            'book' => [
                'id' => $book['id'],
                'title' => $book['title'],
                'description' => $book['description'],
                'has_pdf' => !empty($book['pdf_file']),
                'has_vector' => (bool)$book['has_vector'],
                'total_pages' => $book['total_pages'],
                'processed_at' => $book['processed_at'],
            ],
            'ai_available' => (bool)$book['has_vector'],
            'vector_stats' => $vectorStats,
        ]);
    }

    /**
     * Get suggested questions for a book
     * GET /ai/suggestions/{id}
     */
    public function suggestions($id)
    {
        $authCheck = $this->checkAuth();
        if ($authCheck) return $authCheck;

        $book = $this->bookModel->find($id);
        
        if (!$book) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Buku tidak ditemukan',
            ])->setStatusCode(404);
        }

        // Generate suggested questions based on book
        $suggestions = [
            "Apa tema utama dari buku ini?",
            "Siapa tokoh utama dalam buku ini?",
            "Apa yang bisa saya pelajari dari buku ini?",
            "Bisakah kamu meringkas isi buku ini?",
            "Apa kesimpulan dari buku ini?",
        ];

        return $this->response->setJSON([
            'success' => true,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Check AI service configuration
     * GET /ai/status
     */
    public function status()
    {
        // Only allow admin to check status
        if ($this->session->get('role') !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Akses ditolak',
            ])->setStatusCode(403);
        }

        $config = $this->vectorStore->checkConfiguration();

        return $this->response->setJSON([
            'success' => true,
            'services' => [
                'qdrant' => [
                    'status' => $config['qdrant'] ? 'connected' : 'disconnected',
                    'host' => getenv('QDRANT_HOST') ?: 'qdrant',
                    'port' => getenv('QDRANT_PORT') ?: 6333,
                ],
                'deepseek' => [
                    'status' => $config['deepseek'] ? 'configured' : 'not configured',
                    'api_key_set' => !empty(getenv('DEEPSEEK_API_KEY')),
                ],
            ],
        ]);
    }
}
