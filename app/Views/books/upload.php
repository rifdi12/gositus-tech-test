<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h2 class="h4 mb-2">
                            <i class="bi bi-upload"></i> Upload Buku Baru
                        </h2>
                        <p class="text-muted">Tambahkan buku baru ke dalam katalog E-Library</p>
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

                    <form method="POST" action="/books/store" enctype="multipart/form-data" id="uploadForm">
                        <?= csrf_field() ?>
                        
                        <!-- Basic Info First -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Nama Buku <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-book"></i>
                                </span>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?= old('title') ?>" required maxlength="255" placeholder="Masukkan judul buku">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="4" maxlength="1000" placeholder="Deskripsikan buku ini..."><?= old('description') ?></textarea>
                            <small class="form-text text-muted">
                                <span id="charCount">0</span>/1000 karakter
                            </small>
                        </div>

                        <hr class="my-4">
                        <h6 class="mb-3"><i class="bi bi-cloud-upload"></i> Upload Files</h6>
                        
                        <!-- Image Upload -->
                        <div class="mb-3">
                            <label for="image" class="form-label">
                                <i class="bi bi-image"></i> Gambar Cover Bukuass
                            </label>
                            <input type="file" class="form-control" id="image" name="image" 
                                   accept="image/*" onchange="previewImage(this)">
                            <small class="form-text text-muted">
                                Format: JPG, JPEG, PNG, GIF. Maksimal 2MB
                            </small>
                            
                            <!-- Image Preview -->
                            <div id="imagePreview" class="mt-3 text-center" style="display: none;">
                                <img id="preview" class="img-thumbnail" style="max-height: 200px;" alt="Preview">
                            </div>
                        </div>

                        <!-- PDF Upload -->
                        <div class="mb-3">
                            <label for="pdf_file" class="form-label">
                                <i class="bi bi-file-pdf-fill text-danger"></i> File PDF / E-Book 
                                <span class="badge bg-primary">
                                    <i class="bi bi-robot"></i> AI-Ready
                                </span>
                                <span class="badge bg-success">Optional</span>
                            </label>
                            <input type="file" class="form-control" id="pdf_file" name="pdf_file" 
                                   accept=".pdf" onchange="showPdfInfo(this)">
                            <small class="form-text text-muted">
                                📄 Upload file PDF untuk mengaktifkan fitur <strong>Tanya AI</strong><br>
                                Maksimal 20MB. PDF akan diproses otomatis untuk chat AI.
                            </small>
                            <div id="pdfInfo" class="mt-2" style="display: none;">
                                <div class="alert alert-success py-2 mb-0">
                                    <i class="bi bi-check-circle"></i> 
                                    <strong id="pdfName"></strong> (<span id="pdfSize"></span>)
                                    <br><small>✨ Buku ini akan memiliki fitur AI Chat setelah diupload</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-upload"></i> Upload Buku
                            </button>
                            <a href="/dashboard" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali ke Katalog
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Upload Tips -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-lightbulb"></i> Tips Upload
                    </h6>
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li><i class="bi bi-check-circle text-success"></i> Gunakan gambar dengan kualitas baik</li>
                        <li><i class="bi bi-check-circle text-success"></i> Tulis nama buku dengan jelas</li>
                        <li><i class="bi bi-check-circle text-success"></i> Tambahkan deskripsi yang menarik</li>
                        <li><i class="bi bi-check-circle text-success"></i> Pastikan format gambar yang didukung</li>
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
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const submitBtn = document.getElementById('submitBtn');
    
    if (!title) {
        e.preventDefault();
        alert('Nama buku harus diisi');
        return false;
    }
    
    // Disable submit button to prevent double submission
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';
});
</script>
<?= $this->endSection() ?>