<?php require_once BASEPATH . '/app/Views/partials/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Masuk ke Akun</h2>
                        <p class="text-muted">Masuk untuk mengelola donasi dan kampanye Anda</p>
                    </div>

                    <?php if (isset($_SESSION['flash']['error'])): ?>
                        <div class="alert alert-danger mb-4">
                            <i class="fas fa-exclamation-circle me-2"></i> <?= $_SESSION['flash']['error'] ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['flash']['success'])): ?>
                        <div class="alert alert-success mb-4">
                            <i class="fas fa-check-circle me-2"></i> <?= $_SESSION['flash']['success'] ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>/auth/doLogin" method="post">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-envelope text-muted"></i>
                                </span>
                                <input type="email" class="form-control border-start-0" id="email" name="email" 
                                       placeholder="Masukkan email Anda" required
                                       value="<?= $_SESSION['flash']['old']['email'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between">
                                <label for="password" class="form-label">Kata Sandi</label>
                                <a href="<?= BASE_URL ?>/auth/forgot-password" class="text-decoration-none small">Lupa kata sandi?</a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" class="form-control border-start-0" id="password" name="password" 
                                       placeholder="Masukkan kata sandi Anda" required>
                            </div>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1" 
                                   <?= isset($_SESSION['flash']['old']['remember']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="remember">Ingat saya</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 mb-4">Masuk</button>

                        <div class="text-center">
                            <p class="mb-0">Belum punya akun? <a href="<?= BASE_URL ?>/auth/register" class="text-decoration-none">Daftar sekarang</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once BASEPATH . '/app/Views/partials/footer.php'; ?> 