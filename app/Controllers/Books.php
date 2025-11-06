<?php

namespace App\Controllers;

use App\Models\BookModel;
use CodeIgniter\Controller;

class Books extends BaseController
{
    protected $bookModel;
    protected $session;

    public function __construct()
    {
        $this->bookModel = new BookModel();
        $this->session = \Config\Services::session();
    }

    private function checkAdminAccess()
    {
        if (!$this->session->get('logged_in') || $this->session->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak. Hanya admin yang dapat mengakses fitur ini.');
        }
        return null;
    }

    public function upload()
    {
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;

        $data = [
            'title' => 'Upload Buku - E-Library',
            'validation' => \Config\Services::validation()
        ];

        return view('books/upload', $data);
    }

    public function store()
    {
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;

        $rules = [
            'title' => 'required|max_length[255]',
            'description' => 'permit_empty|max_length[1000]',
            'image' => 'permit_empty|uploaded[image]|is_image[image]|max_size[image,2048]|ext_in[image,jpg,jpeg,png,gif]',
            'pdf_file' => 'permit_empty|uploaded[pdf_file]|ext_in[pdf_file,pdf]|max_size[pdf_file,20480]'
        ];

        $messages = [
            'title' => [
                'required' => 'Nama buku harus diisi',
                'max_length' => 'Nama buku maksimal 255 karakter'
            ],
            'description' => [
                'max_length' => 'Deskripsi maksimal 1000 karakter'
            ],
            'image' => [
                'uploaded' => 'Gambar harus dipilih',
                'is_image' => 'File harus berupa gambar',
                'max_size' => 'Ukuran gambar maksimal 2MB',
                'ext_in' => 'Format gambar harus jpg, jpeg, png, atau gif'
            ],
            'pdf_file' => [
                'ext_in' => 'File harus berformat PDF',
                'max_size' => 'Ukuran PDF maksimal 20MB'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $image = $this->request->getFile('image');
        $imageName = null;
        $pdfFile = $this->request->getFile('pdf_file');
        $pdfFileName = null;

        // Handle image upload
        if ($image && $image->isValid() && !$image->hasMoved()) {
            $imageName = $image->getRandomName();
            $uploadPath = FCPATH . 'uploads/books/';
            
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            $image->move($uploadPath, $imageName);
        }

        // Handle PDF upload
        if ($pdfFile && $pdfFile->isValid() && !$pdfFile->hasMoved()) {
            $pdfFileName = $pdfFile->getRandomName();
            $pdfUploadPath = FCPATH . 'uploads/pdfs/';
            
            if (!is_dir($pdfUploadPath)) {
                mkdir($pdfUploadPath, 0755, true);
            }
            
            $pdfFile->move($pdfUploadPath, $pdfFileName);
            log_message('info', "PDF uploaded: {$pdfFileName}");
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'image' => $imageName,
            'pdf_file' => $pdfFileName,
            'uploaded_by' => $this->session->get('user_id')
        ];

        log_message('info', "Inserting book with data: " . json_encode($data));
        $bookId = $this->bookModel->insert($data);
        log_message('info', "Book inserted with ID: {$bookId}");

        if ($bookId) {
            // Process PDF in background if uploaded
            if ($pdfFileName) {
                log_message('info', "Starting PDF processing for book {$bookId}, file: {$pdfFileName}");
                try {
                    $this->processPdfAsync($bookId, $pdfUploadPath . $pdfFileName);
                    log_message('info', "PDF processing initiated for book {$bookId}");
                } catch (\Exception $e) {
                    log_message('error', "Failed to initiate PDF processing: " . $e->getMessage());
                }
            }

            return redirect()->to('/dashboard')->with('success', 'Buku berhasil di-upload' . ($pdfFileName ? '. PDF sedang diproses untuk fitur AI.' : ''));
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal meng-upload buku');
        }
    }

    /**
     * Process PDF and create vector embeddings
     */
    private function processPdfAsync($bookId, $pdfPath)
    {
        try {
            $vectorStore = new \App\Libraries\VectorStoreService();
            
            $book = $this->bookModel->find($bookId);
            $metadata = [
                'title' => $book['title'],
                'description' => $book['description'] ?? '',
            ];

            $result = $vectorStore->processAndStoreBook($pdfPath, $bookId, $metadata);

            if ($result['success']) {
                // Update book record with vector info
                $updateData = [
                    'id' => $bookId,
                    'has_vector' => 1,
                    'collection_name' => $result['collection_name'],
                    'total_pages' => $result['pages'],
                    'processed_at' => date('Y-m-d H:i:s'),
                ];
                
                $this->bookModel->save($updateData);
                log_message('info', "PDF processed successfully for book {$bookId}, collection: {$result['collection_name']}");
            } else {
                log_message('error', "PDF processing failed for book {$bookId}: " . ($result['error'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            log_message('error', "PDF processing exception for book {$bookId}: " . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;

        $book = $this->bookModel->find($id);

        if (!$book) {
            return redirect()->to('/dashboard')->with('error', 'Buku tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Buku - E-Library',
            'book' => $book,
            'validation' => \Config\Services::validation()
        ];

        return view('books/edit', $data);
    }

    public function update($id)
    {
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;

        $book = $this->bookModel->find($id);

        if (!$book) {
            return redirect()->to('/dashboard')->with('error', 'Buku tidak ditemukan');
        }

        $rules = [
            'title' => 'required|max_length[255]',
            'description' => 'permit_empty|max_length[1000]',
            'image' => 'permit_empty|is_image[image]|max_size[image,2048]|ext_in[image,jpg,jpeg,png,gif]',
            'pdf_file' => 'permit_empty|ext_in[pdf_file,pdf]|max_size[pdf_file,20480]'
        ];

        $messages = [
            'title' => [
                'required' => 'Nama buku harus diisi',
                'max_length' => 'Nama buku maksimal 255 karakter'
            ],
            'description' => [
                'max_length' => 'Deskripsi maksimal 1000 karakter'
            ],
            'image' => [
                'is_image' => 'File harus berupa gambar',
                'max_size' => 'Ukuran gambar maksimal 2MB',
                'ext_in' => 'Format gambar harus jpg, jpeg, png, atau gif'
            ],
            'pdf_file' => [
                'ext_in' => 'File harus berformat PDF',
                'max_size' => 'Ukuran PDF maksimal 20MB'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $image = $this->request->getFile('image');
        $imageName = $book['image']; // Keep existing image by default

        if ($image && $image->isValid() && !$image->hasMoved()) {
            // Delete old image if exists
            if ($book['image'] && file_exists(FCPATH . 'uploads/books/' . $book['image'])) {
                unlink(FCPATH . 'uploads/books/' . $book['image']);
            }

            $imageName = $image->getRandomName();
            $uploadPath = FCPATH . 'uploads/books/';
            
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            $image->move($uploadPath, $imageName);
        }

        // Handle optional PDF upload on edit
        $pdfFile = $this->request->getFile('pdf_file');
        $pdfFileName = $book['pdf_file']; // keep existing by default
        $newPdfUploaded = false;

        if ($pdfFile && $pdfFile->isValid() && !$pdfFile->hasMoved()) {
            // Delete old PDF if exists
            if (!empty($book['pdf_file']) && file_exists(FCPATH . 'uploads/pdfs/' . $book['pdf_file'])) {
                @unlink(FCPATH . 'uploads/pdfs/' . $book['pdf_file']);
            }

            $pdfFileName = $pdfFile->getRandomName();
            $pdfUploadPath = FCPATH . 'uploads/pdfs/';

            if (!is_dir($pdfUploadPath)) {
                mkdir($pdfUploadPath, 0755, true);
            }

            $pdfFile->move($pdfUploadPath, $pdfFileName);
            $newPdfUploaded = true;
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'image' => $imageName,
            'pdf_file' => $pdfFileName
        ];

        if ($this->bookModel->update($id, $data)) {
            // If new PDF uploaded, process it for AI vectors
            if ($newPdfUploaded) {
                $this->processPdfAsync($id, FCPATH . 'uploads/pdfs/' . $pdfFileName);
                return redirect()->to('/dashboard')->with('success', 'Buku diperbarui. PDF baru diproses untuk fitur AI.');
            }
            return redirect()->to('/dashboard')->with('success', 'Buku berhasil diperbarui');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui buku');
        }
    }

    public function delete($id)
    {
        $redirect = $this->checkAdminAccess();
        if ($redirect) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $book = $this->bookModel->find($id);

        if (!$book) {
            return $this->response->setJSON(['success' => false, 'message' => 'Buku tidak ditemukan']);
        }

        // Delete image file if exists
        if ($book['image'] && file_exists(FCPATH . 'uploads/books/' . $book['image'])) {
            unlink(FCPATH . 'uploads/books/' . $book['image']);
        }

        if ($this->bookModel->delete($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Buku berhasil dihapus']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal menghapus buku']);
        }
    }

    /**
     * Show book detail with AI chat interface
     */
    public function detail($id)
    {
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/login');
        }

        $book = $this->bookModel->find($id);

        if (!$book) {
            return redirect()->to('/dashboard')->with('error', 'Buku tidak ditemukan');
        }

        $data = [
            'title' => $book['title'] . ' - E-Library',
            'book' => $book,
        ];

        return view('books/detail', $data);
    }
}