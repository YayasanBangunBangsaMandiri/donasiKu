<?php require_once BASEPATH . '/app/Views/admin/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="fw-bold"><?= $isEdit ? 'Edit Kampanye' : 'Tambah Kampanye Baru' ?></h2>
            <a href="<?= BASE_URL ?>/admin/campaigns" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar Kampanye
            </a>
        </div>
    </div>
    
    <?php if (isset($_SESSION['flash']['errors'])): ?>
        <div class="alert alert-danger">
            <h5><i class="fas fa-exclamation-circle me-2"></i> Ada kesalahan pada form:</h5>
            <ul class="mb-0">
                <?php foreach ($_SESSION['flash']['errors'] as $field => $errors): ?>
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i> <?= $_SESSION['flash']['success'] ?>
        </div>
    <?php endif; ?>
    
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="<?= BASE_URL ?>/admin/<?= $isEdit ? 'update-campaign/' . $campaign['id'] : 'create-campaign' ?>" method="post" enctype="multipart/form-data">
                <div class="row g-4">
                    <!-- Informasi Dasar -->
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0 py-3">
                                <h5 class="mb-0">Informasi Dasar</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Judul Kampanye <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" required
                                           value="<?= $isEdit ? $campaign['title'] : ($_SESSION['flash']['old']['title'] ?? '') ?>">
                                    <div class="form-text">Judul yang menarik dan jelas mengenai tujuan kampanye.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">-- Pilih Kategori --</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>" <?= ($isEdit && $campaign['category_id'] == $category['id']) || (!$isEdit && isset($_SESSION['flash']['old']['category_id']) && $_SESSION['flash']['old']['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                                <?= $category['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="short_description" class="form-label">Deskripsi Singkat <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="short_description" name="short_description" rows="2" required><?= $isEdit ? $campaign['short_description'] : ($_SESSION['flash']['old']['short_description'] ?? '') ?></textarea>
                                    <div class="form-text">Maksimal 255 karakter.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi Lengkap <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="description" name="description" rows="10" required><?= $isEdit ? $campaign['description'] : ($_SESSION['flash']['old']['description'] ?? '') ?></textarea>
                                    <div class="form-text">Jelaskan secara detail mengenai kampanye ini, mengapa penting, dan bagaimana dana akan digunakan.</div>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="start_date" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" required
                                               value="<?= $isEdit ? date('Y-m-d', strtotime($campaign['start_date'])) : ($_SESSION['flash']['old']['start_date'] ?? date('Y-m-d')) ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="end_date" class="form-label">Tanggal Berakhir <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" required
                                               value="<?= $isEdit ? date('Y-m-d', strtotime($campaign['end_date'])) : ($_SESSION['flash']['old']['end_date'] ?? date('Y-m-d', strtotime('+30 days'))) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Gambar & Pengaturan -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-transparent border-0 py-3">
                                <h5 class="mb-0">Gambar Kampanye</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="featured_image" class="form-label">Gambar Utama <span class="text-danger">*</span></label>
                                    <?php if ($isEdit && !empty($campaign['featured_image'])): ?>
                                        <div class="mb-2">
                                            <img src="<?= BASE_URL ?>/public/uploads/<?= $campaign['featured_image'] ?>" class="img-thumbnail d-block mb-2" style="max-height: 200px;">
                                            <small class="text-muted">Gambar saat ini</small>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*" <?= $isEdit ? '' : 'required' ?>>
                                    <div class="form-text">Format: JPG, PNG, atau GIF. Maks. <?= MAX_UPLOAD_SIZE / (1024*1024) ?>MB.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="banner_image" class="form-label">Gambar Banner (Opsional)</label>
                                    <?php if ($isEdit && !empty($campaign['banner_image'])): ?>
                                        <div class="mb-2">
                                            <img src="<?= BASE_URL ?>/public/uploads/<?= $campaign['banner_image'] ?>" class="img-thumbnail d-block mb-2" style="max-height: 100px;">
                                            <small class="text-muted">Banner saat ini</small>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="banner_image" name="banner_image" accept="image/*">
                                    <div class="form-text">Format: JPG, PNG, atau GIF. Maks. <?= MAX_UPLOAD_SIZE / (1024*1024) ?>MB.</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-transparent border-0 py-3">
                                <h5 class="mb-0">Target Donasi</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="goal_amount" class="form-label">Target Donasi <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" id="goal_amount" name="goal_amount" required min="100000"
                                               value="<?= $isEdit ? $campaign['goal_amount'] : ($_SESSION['flash']['old']['goal_amount'] ?? '1000000') ?>">
                                    </div>
                                    <div class="form-text">Minimal Rp 100.000</div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="allow_custom_amount" name="allow_custom_amount" value="1"
                                               <?= ($isEdit && $campaign['allow_custom_amount']) || (!$isEdit && isset($_SESSION['flash']['old']['allow_custom_amount'])) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="allow_custom_amount">Izinkan donasi dengan jumlah kustom</label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="preset_amounts" class="form-label">Jumlah Donasi Preset (Opsional)</label>
                                    <input type="text" class="form-control" id="preset_amounts" name="preset_amounts"
                                           value="<?= $isEdit && !empty($campaign['donation_amounts']) ? implode(',', array_keys(json_decode($campaign['donation_amounts'], true))) : ($_SESSION['flash']['old']['preset_amounts'] ?? '50000,100000,500000,1000000') ?>">
                                    <div class="form-text">Masukkan nilai donasi yang dipisahkan koma (mis: 50000,100000,500000)</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="donation_info" class="form-label">Info Donasi (Opsional)</label>
                                    <textarea class="form-control" id="donation_info" name="donation_info" rows="2"><?= $isEdit ? $campaign['donation_info'] : ($_SESSION['flash']['old']['donation_info'] ?? '') ?></textarea>
                                    <div class="form-text">Mis: "100rb = 1 paket makanan untuk korban bencana"</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent border-0 py-3">
                                <h5 class="mb-0">Pengaturan</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status Kampanye</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="pending" <?= ($isEdit && $campaign['status'] == 'pending') || (!$isEdit && isset($_SESSION['flash']['old']['status']) && $_SESSION['flash']['old']['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                                        <option value="active" <?= ($isEdit && $campaign['status'] == 'active') || (!$isEdit && isset($_SESSION['flash']['old']['status']) && $_SESSION['flash']['old']['status'] == 'active') ? 'selected' : '' ?>>Aktif</option>
                                        <option value="completed" <?= ($isEdit && $campaign['status'] == 'completed') || (!$isEdit && isset($_SESSION['flash']['old']['status']) && $_SESSION['flash']['old']['status'] == 'completed') ? 'selected' : '' ?>>Selesai</option>
                                        <option value="rejected" <?= ($isEdit && $campaign['status'] == 'rejected') || (!$isEdit && isset($_SESSION['flash']['old']['status']) && $_SESSION['flash']['old']['status'] == 'rejected') ? 'selected' : '' ?>>Ditolak</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1"
                                               <?= ($isEdit && $campaign['is_featured']) || (!$isEdit && isset($_SESSION['flash']['old']['is_featured'])) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="is_featured">Tampilkan di halaman utama</label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="meta_tags" class="form-label">Meta Tags (Opsional)</label>
                                    <input type="text" class="form-control" id="meta_tags" name="meta_tags"
                                           value="<?= $isEdit ? $campaign['meta_tags'] : ($_SESSION['flash']['old']['meta_tags'] ?? '') ?>">
                                    <div class="form-text">Kata kunci yang dipisahkan koma untuk SEO</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tombol Submit -->
                    <div class="col-12 text-end">
                        <hr>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i> <?= $isEdit ? 'Perbarui Kampanye' : 'Buat Kampanye' ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once BASEPATH . '/app/Views/admin/footer.php'; ?>
