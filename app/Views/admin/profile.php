<?php require_once BASEPATH . '/app/Views/admin/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Profil Admin</h4>
                </div>
                <div class="card-body">
                    <!-- Display validation errors if any -->
                    <?php if (isset($_SESSION['errors'])): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($_SESSION['errors'] as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php unset($_SESSION['errors']); ?>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>/admin/updateProfile" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-3 text-center mb-4">
                                <?php 
                                $profilePic = isset($user['profile_picture']) && !empty($user['profile_picture']) 
                                    ? $user['profile_picture'] 
                                    : 'default.jpg';
                                ?>
                                <img src="<?= BASE_URL ?>/public/uploads/<?= $profilePic ?>" 
                                     alt="Profile Picture" 
                                     class="img-fluid rounded-circle mb-3" 
                                     style="width: 150px; height: 150px; object-fit: cover;">
                                
                                <div class="mb-3">
                                    <label for="profile_picture" class="form-label">Foto Profil</label>
                                    <input type="file" class="form-control" id="profile_picture" name="profile_picture">
                                    <div class="form-text">Format: JPG, PNG, GIF. Maks: 2MB</div>
                                </div>
                            </div>
                            
                            <div class="col-md-9">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Nama</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Telepon</label>
                                        <input type="text" class="form-control" id="phone" name="phone" 
                                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="role" class="form-label">Role</label>
                                        <input type="text" class="form-control" id="role" 
                                               value="<?= htmlspecialchars(ucfirst($user['role'] ?? 'Admin')) ?>" 
                                               disabled>
                                    </div>
                                    
                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                                        </button>
                                        <a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-secondary ms-2">
                                            <i class="fas fa-arrow-left me-1"></i> Kembali
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once BASEPATH . '/app/Views/admin/footer.php'; ?> 