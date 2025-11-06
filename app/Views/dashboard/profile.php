<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-person-fill text-white" style="font-size: 2rem;"></i>
                        </div>
                        <h3 class="mb-1"><?= esc($user['email']) ?></h3>
                        <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'primary' ?> mb-3">
                            <?= $user['role'] === 'admin' ? 'Administrator' : 'User' ?>
                        </span>
                    </div>

                    <div class="row text-center mb-4">
                        <div class="col-6">
                            <div class="card bg-light">
                                <div class="card-body py-3">
                                    <h4 class="text-primary mb-1"><?= $totalFavorites ?></h4>
                                    <small class="text-muted">Buku Favorit</small>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($user['role'] === 'admin'): ?>
                        <div class="col-6">
                            <div class="card bg-light">
                                <div class="card-body py-3">
                                    <h4 class="text-success mb-1"><?= $totalUploads ?></h4>
                                    <small class="text-muted">Buku Upload</small>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Email</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" class="form-control" value="<?= esc($user['email']) ?>" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Role</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-shield-check"></i>
                            </span>
                            <input type="text" class="form-control" 
                                   value="<?= $user['role'] === 'admin' ? 'Administrator' : 'User' ?>" readonly>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted">Bergabung Sejak</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-calendar"></i>
                            </span>
                            <input type="text" class="form-control" 
                                   value="<?= date('d F Y', strtotime($user['created_at'])) ?>" readonly>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="/favorites" class="btn btn-outline-primary">
                            <i class="bi bi-heart"></i> Lihat Favorit
                        </a>
                        
                        <?php if ($user['role'] === 'admin'): ?>
                        <a href="/books/upload" class="btn btn-outline-success">
                            <i class="bi bi-upload"></i> Upload Buku
                        </a>
                        <?php endif; ?>
                        
                        <a href="/logout" class="btn btn-outline-danger">
                            <i class="bi bi-box-arrow-right"></i> Keluar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-info-circle"></i> Informasi Akun
                    </h6>
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li><i class="bi bi-check-circle text-success"></i> Akun Anda telah aktif</li>
                        <li><i class="bi bi-shield-check text-primary"></i> Data Anda aman dan terlindungi</li>
                        <?php if ($user['role'] === 'admin'): ?>
                        <li><i class="bi bi-star text-warning"></i> Anda memiliki hak akses Administrator</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>