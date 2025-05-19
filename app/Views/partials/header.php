<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title : APP_NAME . ' - Platform Donasi Online' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/public/img/favicon.ico" type="image/x-icon">
</head>
<body>
    <!-- Header/Navbar -->
    <header class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>">
                <img src="<?= BASE_URL ?>/public/img/logo.png" alt="<?= APP_NAME ?>" height="40">
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/campaign">Kampanye</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            Kategori
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/campaign/category/kesehatan">Kesehatan</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/campaign/category/pendidikan">Pendidikan</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/campaign/category/bencana-alam">Bencana Alam</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/campaign/category/sosial">Sosial</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/campaign/category/lingkungan">Lingkungan</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/about">Tentang Kami</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/contact">Hubungi Kami</a>
                    </li>
                </ul>
                
                <div class="d-flex">
                    <?php if (isset($_SESSION['user'])): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i> <?= $_SESSION['user']['name'] ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-tachometer-alt me-2"></i> Admin Dashboard</a></li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/campaigns"><i class="fas fa-hand-holding-heart me-2"></i> Kelola Kampanye</a></li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/donations"><i class="fas fa-donate me-2"></i> Kelola Donasi</a></li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/settings"><i class="fas fa-cog me-2"></i> Pengaturan</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php else: ?>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/dashboard"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/profile"><i class="fas fa-user me-2"></i> Profil</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/campaigns"><i class="fas fa-hand-holding-heart me-2"></i> Kampanye Saya</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/donations"><i class="fas fa-donate me-2"></i> Donasi Saya</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/auth/logout"><i class="fas fa-sign-out-alt me-2"></i> Keluar</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/auth/login" class="btn btn-outline-primary me-2">Masuk</a>
                        <a href="<?= BASE_URL ?>/auth/register" class="btn btn-primary">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <!-- Main Content -->
</body>
</html> 