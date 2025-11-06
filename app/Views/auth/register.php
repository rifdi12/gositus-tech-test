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
                        <p class="text-muted">Buat akun baru</p>
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

                    <form method="POST" action="/register" id="registerForm">
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
                            <small class="form-text text-muted">
                                Minimal 8 karakter, harus mengandung huruf besar, huruf kecil, dan angka
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" required>
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="togglePassword('confirm_password')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div id="password-match-message" class="mt-1"></div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary" id="registerBtn">
                                <i class="bi bi-person-plus"></i> Daftar Akun
                            </button>
                        </div>
                    </form>

                    <div class="text-center">
                        <p class="mb-0">
                            Sudah punya akun? 
                            <a href="/login" class="text-decoration-none">Masuk</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const message = document.getElementById('password-match-message');
    const registerBtn = document.getElementById('registerBtn');

    function checkPasswordMatch() {
        if (confirmPassword.value === '') {
            message.innerHTML = '';
            return;
        }

        if (password.value === confirmPassword.value) {
            message.innerHTML = '<small class="text-success"><i class="bi bi-check-circle"></i> Password cocok</small>';
            registerBtn.disabled = false;
        } else {
            message.innerHTML = '<small class="text-danger"><i class="bi bi-x-circle"></i> Password tidak cocok</small>';
            registerBtn.disabled = true;
        }
    }

    function checkPasswordStrength() {
        const passwordValue = password.value;
        const hasUpperCase = /[A-Z]/.test(passwordValue);
        const hasLowerCase = /[a-z]/.test(passwordValue);
        const hasNumbers = /\d/.test(passwordValue);
        const minLength = passwordValue.length >= 8;

        let strengthText = '';
        let strengthClass = '';

        if (passwordValue.length === 0) {
            return;
        }

        if (minLength && hasUpperCase && hasLowerCase && hasNumbers) {
            strengthText = 'Password kuat';
            strengthClass = 'text-success';
        } else {
            strengthText = 'Password lemah - ';
            strengthClass = 'text-warning';
            
            const missing = [];
            if (!minLength) missing.push('min 8 karakter');
            if (!hasUpperCase) missing.push('huruf besar');
            if (!hasLowerCase) missing.push('huruf kecil');
            if (!hasNumbers) missing.push('angka');
            
            strengthText += 'perlu: ' + missing.join(', ');
        }

        const existingStrength = password.parentElement.parentElement.querySelector('.password-strength');
        if (existingStrength) {
            existingStrength.remove();
        }

        const strengthElement = document.createElement('small');
        strengthElement.className = `form-text ${strengthClass} password-strength`;
        strengthElement.innerHTML = strengthText;
        password.parentElement.parentElement.appendChild(strengthElement);
    }

    password.addEventListener('input', checkPasswordStrength);
    confirmPassword.addEventListener('input', checkPasswordMatch);
    password.addEventListener('input', checkPasswordMatch);
});
</script>
<?= $this->endSection() ?>