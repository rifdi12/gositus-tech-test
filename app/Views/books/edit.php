<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h2 class="h4 mb-2">
                            <i class="bi bi-pencil"></i> Edit Buku
                        </h2>
                        <p class="text-muted">Perbarui informasi buku</p>
                    </div>

                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/books/update/<?= $book['id'] ?>" enctype="multipart/form-data" id="editForm">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Gambar Buku</label>
                            
                            <!-- Current Image -->
                            <?php if (!empty($book['image'])): ?>
                            <div class="mb-2">
                                <img src="/uploads/books/<?= esc($book['image']) ?>" 
                                     class="img-thumbnail" style="max-height: 150px;" alt="Current Image">
                                <small class="d-block text-muted">Gambar saat ini</small>
                            </div>
                            <?php endif; ?>
                            
                            <div class="input-group">
                                <input type="file" class="form-control" id="image" name="image" 
                                       accept="image/*" onchange="previewImage(this)">
                                <span class="input-group-text">
                                    <i class="bi bi-image"></i>
                                </span>
                            </div>
                            <small class="form-text text-muted">
                                Format: JPG, JPEG, PNG, GIF. Maksimal 2MB. Biarkan kosong jika tidak ingin mengubah gambar.
                            </small>
                            
                            <!-- New Image Preview -->
                            <div id="imagePreview" class="mt-3 text-center" style="display: none;">
                                <img id="preview" class="img-thumbnail" style="max-height: 200px;" alt="Preview">
                                <small class="d-block text-muted">Gambar baru</small>
                            </div>
                        </div>

                        <!-- PDF Upload (Optional) -->
                        <div class="mb-3">
                            <label for="pdf_file" class="form-label">
                                <i class="bi bi-file-pdf-fill text-danger"></i> File PDF / E-Book
                                <span class="badge bg-primary ms-1"><i class="bi bi-robot"></i> AI-Ready</span>
                                <span class="badge bg-success ms-1">Optional</span>
                            </label>

                            <?php if (!empty($book['pdf_file'])): ?>
                                <div class="alert alert-info py-2 mb-2">
                                    <i class="bi bi-file-earmark-text"></i>
                                    PDF saat ini: <strong><?= esc($book['pdf_file']) ?></strong>
                                    <?php if (!empty($book['has_vector'])): ?>
                                        <span class="badge bg-primary ms-2"><i class="bi bi-robot"></i> AI</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark ms-2"><i class="bi bi-clock"></i> Diproses</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <input type="file" class="form-control" id="pdf_file" name="pdf_file" 
                                   accept=".pdf" onchange="showPdfInfo(this)">
                            <small class="form-text text-muted">
                                Upload PDF baru untuk mengganti yang lama (maks 20MB). PDF akan diproses untuk fitur Tanya AI.
                            </small>
                            <div id="pdfInfo" class="mt-2" style="display: none;">
                                <div class="alert alert-success py-2 mb-0">
                                    <i class="bi bi-check-circle"></i>
                                    <strong id="pdfName"></strong> (<span id="pdfSize"></span>)
                                    <br><small>✨ Buku ini akan memiliki fitur AI Chat setelah diupload</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Nama Buku <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-book"></i>
                                </span>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?= old('title', $book['title']) ?>" required maxlength="255">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="4" maxlength="1000" 
                                      placeholder="Deskripsikan buku ini..."><?= old('description', $book['description']) ?></textarea>
                            <small class="form-text text-muted">
                                <span id="charCount">0</span>/1000 karakter
                            </small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-check-circle"></i> Perbarui Buku
                            </button>
                            <a href="/dashboard" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali ke Katalog
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Book Info -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-info-circle"></i> Informasi Buku
                    </h6>
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li><strong>ID:</strong> <?= $book['id'] ?></li>
                        <li><strong>Dibuat:</strong> <?= date('d M Y H:i', strtotime($book['created_at'])) ?></li>
                        <li><strong>Terakhir diperbarui:</strong> <?= date('d M Y H:i', strtotime($book['updated_at'])) ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Image preview
function previewImage(input) {
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        previewContainer.style.display = 'none';
    }
}

// PDF file info
function showPdfInfo(input) {
    const pdfInfo = document.getElementById('pdfInfo');
    const pdfName = document.getElementById('pdfName');
    const pdfSize = document.getElementById('pdfSize');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        
        pdfName.textContent = file.name;
        pdfSize.textContent = sizeMB + ' MB';
        pdfInfo.style.display = 'block';
    } else {
        pdfInfo.style.display = 'none';
    }
}

// Character count for description
document.addEventListener('DOMContentLoaded', function() {
    const description = document.getElementById('description');
    const charCount = document.getElementById('charCount');
    
    function updateCharCount() {
        const count = description.value.length;
        charCount.textContent = count;
        
        if (count > 900) {
            charCount.className = 'text-warning';
        } else if (count > 1000) {
            charCount.className = 'text-danger';
        } else {
            charCount.className = '';
        }
    }
    
    description.addEventListener('input', updateCharCount);
    updateCharCount(); // Initial count
});

// Form validation
document.getElementById('editForm').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const submitBtn = document.getElementById('submitBtn');
    
    if (!title) {
        e.preventDefault();
        alert('Nama buku harus diisi');
        return false;
    }
    
    // Disable submit button to prevent double submission
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memperbarui...';
});
</script>
<?= $this->endSection() ?>