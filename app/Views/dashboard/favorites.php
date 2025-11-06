<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">
                <i class="bi bi-heart-fill text-danger"></i> Buku Favorit
            </h1>

            <?php if (empty($favorites)): ?>
            <div class="text-center py-5">
                <i class="bi bi-heart display-1 text-muted"></i>
                <h3 class="mt-3 text-muted">Belum ada buku favorit</h3>
                <p class="text-muted">Tambahkan buku ke favorit dengan mengklik ikon hati di katalog</p>
                <a href="/dashboard" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Katalog
                </a>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($favorites as $favorite): ?>
                <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($favorite['image'])): ?>
                        <img src="/uploads/books/<?= esc($favorite['image']) ?>" 
                             class="card-img-top book-image" 
                             alt="<?= esc($favorite['title']) ?>">
                        <?php else: ?>
                        <div class="card-img-top book-image bg-light d-flex align-items-center justify-content-center">
                            <i class="bi bi-book display-4 text-muted"></i>
                        </div>
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0 flex-grow-1"><?= esc($favorite['title']) ?></h6>
                                <button class="favorite-btn ms-2" onclick="toggleFavorite(<?= $favorite['book_id'] ?>)"
                                        style="color: #dc3545">
                                    <i class="bi bi-heart-fill"></i>
                                </button>
                            </div>
                            
                            <?php if (!empty($favorite['description'])): ?>
                            <p class="card-text small text-muted flex-grow-1">
                                <?= esc(strlen($favorite['description']) > 100 ? 
                                    substr($favorite['description'], 0, 100) . '...' : 
                                    $favorite['description']) ?>
                            </p>
                            <?php endif; ?>
                            
                            <div class="mt-auto">
                                <small class="text-muted d-block">
                                    <i class="bi bi-calendar"></i> 
                                    <?= date('d M Y', strtotime($favorite['book_created'])) ?>
                                </small>
                                <small class="text-muted d-block">
                                    <i class="bi bi-heart"></i> 
                                    Ditambahkan <?= date('d M Y', strtotime($favorite['created_at'])) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-4">
                <a href="/dashboard" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Katalog
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function toggleFavorite(bookId) {
    fetch('/favorites/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({book_id: bookId})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to update the favorites list
            location.reload();
        }
    });
}
</script>
<?= $this->endSection() ?>