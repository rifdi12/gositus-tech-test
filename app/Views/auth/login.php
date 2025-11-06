<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
    <div class="row w-100">
        <div class="col-md-6 col-lg-4 mx-auto">
            <div class="card shadow">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-primary">
                            <i class="bi bi-book"></i> E-Library
                        </h2>
                        <p class="text-muted">Masuk ke akun Anda</p>
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

                    <form method="POST" action="/login">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= old('email') ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="togglePassword('password')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right"></i> Masuk
                            </button>
                        </div>
                    </form>

                    <div class="text-center">
                        <p class="mb-0">
                            Belum punya akun? 
                            <a href="/register" class="text-decoration-none">Daftar Akun</a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Demo Account Info -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title text-center">Akun Demo</h6>
                    <div class="row text-center">
                        <div class="col-6">
                            <strong>Admin:</strong><br>
                            <small>admin@elibrary.com</small><br>
                            <small>Admin123</small>
                        </div>
                        <div class="col-6">
                            <strong>User:</strong><br>
                            <small>user@elibrary.com</small><br>
                            <small>User123</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>