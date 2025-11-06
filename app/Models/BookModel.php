<?php

namespace App\Models;

use CodeIgniter\Model;

class BookModel extends Model
{
    protected $table            = 'books';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'title',
        'description',
        'image',
        'uploaded_by',
        // AI/PDF related fields
        'pdf_file',
        'has_vector',
        'collection_name',
        'total_pages',
        'processed_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'title' => 'required|max_length[255]',
        'description' => 'permit_empty|max_length[1000]',
        'uploaded_by' => 'required|integer'
    ];

    protected $validationMessages = [
        'title' => [
            'required' => 'Nama buku harus diisi',
            'max_length' => 'Nama buku maksimal 255 karakter'
        ],
        'description' => [
            'max_length' => 'Deskripsi maksimal 1000 karakter'
        ]
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;

    public function searchBooks($keyword = '')
    {
        if (empty($keyword)) {
            return $this->orderBy('created_at', 'DESC')->findAll();
        }
        
        return $this->like('title', $keyword)
                    ->orLike('description', $keyword)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function getBookWithUploader($id)
    {
        return $this->select('books.*, users.email as uploader_email')
                    ->join('users', 'users.id = books.uploaded_by')
                    ->where('books.id', $id)
                    ->first();
    }

    public function getAllBooksWithUploader()
    {
        return $this->select('books.*, users.email as uploader_email')
                    ->join('users', 'users.id = books.uploaded_by')
                    ->orderBy('books.created_at', 'DESC')
                    ->findAll();
    }
}