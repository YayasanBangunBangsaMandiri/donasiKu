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
                            <a href="<?= BASE_URL ?>/user/profile" class="nav-link active">
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
            <!-- Profile Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Profil Saya</h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>/user/updateProfile" method="post" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-md-3 text-center">
                                <img src="<?= BASE_URL ?>/public/uploads/<?= $user['profile_picture'] ?? 'default.jpg' ?>" 
                                    class="rounded-circle mb-3" alt="Profile" id="profilePreview" width="150" height="150">
                                <div class="mb-3">
                                    <label for="profilePicture" class="form-label">Foto Profil</label>
                                    <input type="file" class="form-control" id="profilePicture" name="profile_picture" onchange="previewImage()">
                                    <div class="form-text">Format: JPG, PNG, GIF. Maks: 2MB</div>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= $user['name'] ?>" required>
                                    <?php if (isset($_SESSION['flash']['errors']['name'])): ?>
                                        <div class="text-danger"><?= $_SESSION['flash']['errors']['name'][0] ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" value="<?= $user['email'] ?>" readonly disabled>
                                    <div class="form-text">Email tidak dapat diubah</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Nomor Telepon</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?= $user['phone'] ?? '' ?>">
                                    <?php if (isset($_SESSION['flash']['errors']['phone'])): ?>
                                        <div class="text-danger"><?= $_SESSION['flash']['errors']['phone'][0] ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Alamat</label>
                                    <textarea class="form-control" id="address" name="address" rows="3"><?= $user['address'] ?? '' ?></textarea>
                                    <?php if (isset($_SESSION['flash']['errors']['address'])): ?>
                                        <div class="text-danger"><?= $_SESSION['flash']['errors']['address'][0] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Change Password Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Ubah Password</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Untuk alasan keamanan, silakan ubah password Anda secara berkala</p>
                    <a href="<?= BASE_URL ?>/user/changePassword" class="btn btn-outline-primary">Ubah Password</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage() {
    const file = document.getElementById('profilePicture').files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
}
</script>

<?php require_once BASEPATH . '/app/Views/partials/footer.php'; ?> 