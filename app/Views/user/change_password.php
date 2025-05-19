<?php require_once BASEPATH . '/app/Views/partials/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <?php 
                        $profilePic = isset($_SESSION['user']['profile_picture']) 
                            ? $_SESSION['user']['profile_picture'] 
                            : 'default.jpg';
                        ?>
                        <img src="<?= BASE_URL ?>/public/uploads/<?= $profilePic ?>" alt="Profile" class="rounded-circle mb-3" width="100" height="100">
                        <h5 class="mb-1"><?= $_SESSION['user']['name'] ?></h5>
                        <p class="text-muted small"><?= $_SESSION['user']['email'] ?></p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>/user/dashboard" class="nav-link">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>/user/profile" class="nav-link">
                                <i class="fas fa-user me-2"></i> Profil Saya
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>/user/donations" class="nav-link">
                                <i class="fas fa-donate me-2"></i> Donasi Saya
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>/user/campaigns" class="nav-link">
                                <i class="fas fa-hand-holding-heart me-2"></i> Kampanye Saya
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= BASE_URL ?>/auth/logout" class="nav-link text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i> Keluar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Change Password Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Ubah Password</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['flash']['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['flash']['success'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['flash']['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['flash']['error'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form action="<?= BASE_URL ?>/user/updatePassword" method="post">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Saat Ini</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <?php if (isset($_SESSION['flash']['errors']['current_password'])): ?>
                                <div class="text-danger"><?= $_SESSION['flash']['errors']['current_password'][0] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <div class="form-text">Password minimal 8 karakter</div>
                            <?php if (isset($_SESSION['flash']['errors']['new_password'])): ?>
                                <div class="text-danger"><?= $_SESSION['flash']['errors']['new_password'][0] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <?php if (isset($_SESSION['flash']['errors']['confirm_password'])): ?>
                                <div class="text-danger"><?= $_SESSION['flash']['errors']['confirm_password'][0] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>/user/profile" class="btn btn-outline-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once BASEPATH . '/app/Views/partials/footer.php'; ?> 