<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="bi bi-grid"></i> Katalog Buku
                </h1>
                
                <?php if (session()->get('role') === 'admin'): ?>
                <a href="/books/upload" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Buku Baru
                </a>
                <?php endif; ?>
            </div>

            <?php if (!empty($search)): ?>
            <div class="alert alert-info">
                <i class="bi bi-search"></i> Hasil pencarian untuk: <strong>"<?= esc($search) ?>"</strong>
                <a href="/dashboard" class="btn btn-sm btn-outline-primary ms-2">Reset</a>
            </div>
            <?php endif; ?>

            <?php if (empty($books)): ?>
            <div class="text-center py-5">
                <i class="bi bi-book display-1 text-muted"></i>
                <h3 class="mt-3 text-muted">
                    <?= !empty($search) ? 'Tidak ada buku yang ditemukan' : 'Belum ada buku tersedia' ?>
                </h3>
                <p class="text-muted">
                    <?= !empty($search) ? 'Coba gunakan kata kunci lain' : 'Buku akan muncul di sini setelah di-upload' ?>
                </p>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($books as $book): ?>
                <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($book['image'])): ?>
                        <img src="/uploads/books/<?= esc($book['image']) ?>" 
                             class="card-img-top book-image" 
                             alt="<?= esc($book['title']) ?>">
                        <?php else: ?>
                        <div class="card-img-top book-image bg-light d-flex align-items-center justify-content-center">
                            <i class="bi bi-book display-4 text-muted"></i>
                        </div>
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0 flex-grow-1"><?= esc($book['title']) ?></h6>
                                <button class="favorite-btn ms-2" onclick="toggleFavorite(<?= $book['id'] ?>)"
                                        style="color: <?= $book['is_favorite'] ? '#dc3545' : '#6c757d' ?>">
                                    <i class="bi <?= $book['is_favorite'] ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                                </button>
                            </div>
                            
                            <?php if (!empty($book['description'])): ?>
                            <p class="card-text small text-muted flex-grow-1">
                                <?= esc(strlen($book['description']) > 100 ? 
                                    substr($book['description'], 0, 100) . '...' : 
                                    $book['description']) ?>
                            </p>
                            <?php endif; ?>
                            
                            <div class="mt-auto">
                                <small class="text-muted d-block mb-2">
                                    <i class="bi bi-calendar"></i> 
                                    <?= date('d M Y', strtotime($book['created_at'])) ?>
                                    <?php if (!empty($book['pdf_file'])): ?>
                                        <span class="badge bg-info ms-1">
                                            <i class="bi bi-file-pdf"></i> PDF
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($book['has_vector'])): ?>
                                        <span class="badge bg-primary ms-1">
                                            <i class="bi bi-robot"></i> AI
                                        </span>
                                    <?php endif; ?>
                                </small>
                                
                                <!-- Tanya AI Button -->
                                <a href="/books/detail/<?= $book['id'] ?>" class="btn btn-sm btn-primary w-100 mb-2">
                                    <i class="bi bi-robot"></i> Tanya AI
                                </a>
                                
                                <?php if (session()->get('role') === 'admin'): ?>
                                <div class="btn-group w-100" role="group">
                                    <a href="/books/edit/<?= $book['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <button onclick="deleteBook(<?= $book['id'] ?>)" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function deleteBook(bookId) {
    if (confirm('Apakah Anda yakin ingin menghapus buku ini?')) {
        fetch(`/books/delete/${bookId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal menghapus buku: ' + data.message);
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan saat menghapus buku');
        });
    }
}
</script>
<?= $this->endSection() ?>