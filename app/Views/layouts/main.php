<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title : APP_NAME ?></title>
    
    <!-- Favicon -->
    <!-- <link rel="icon" href="<?= BASE_URL ?>/public/img/favicon.ico" type="image/x-icon"> -->
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    
    <?php if (isset($extraCss)): ?>
        <?= $extraCss ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>">
                <?= APP_NAME ?>
                <!-- <img src="<?= BASE_URL ?>/public/img/logo.png" alt="Logo" height="40"> -->
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= (!isset($_GET['url']) || $_GET['url'] === '') ? 'active' : '' ?>" href="<?= BASE_URL ?>">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (isset($_GET['url']) && strpos($_GET['url'], 'campaign') === 0) ? 'active' : '' ?>" href="<?= BASE_URL ?>/campaign">Kampanye</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (isset($_GET['url']) && strpos($_GET['url'], 'about') === 0) ? 'active' : '' ?>" href="<?= BASE_URL ?>/about">Tentang Kami</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (isset($_GET['url']) && strpos($_GET['url'], 'contact') === 0) ? 'active' : '' ?>" href="<?= BASE_URL ?>/contact">Kontak</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <?php if (isset($_SESSION['user'])): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= $_SESSION['user']['name'] ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <?php if ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'super_admin'): ?>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-tachometer-alt me-2"></i> Dashboard Admin</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/dashboard"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/donations"><i class="fas fa-donate me-2"></i> Donasi Saya</a></li>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/campaigns"><i class="fas fa-hand-holding-heart me-2"></i> Kampanye Saya</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/profile"><i class="fas fa-user-cog me-2"></i> Profil</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/auth/logout"><i class="fas fa-sign-out-alt me-2"></i> Keluar</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/auth/login" class="btn btn-outline-primary me-2">Masuk</a>
                        <a href="<?= BASE_URL ?>/auth/register" class="btn btn-primary">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i> <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i> <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main>
        <?= $content ?>
    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-white mt-5">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5><?= APP_NAME ?></h5>
                    <p class="text-muted"><?= defined('APP_DESCRIPTION') ? APP_DESCRIPTION : 'Platform donasi online untuk membantu mereka yang membutuhkan' ?></p>
                    <div class="d-flex gap-2 mt-3">
                        <a href="#" class="text-muted"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-youtube fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5>Navigasi</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= BASE_URL ?>" class="text-muted text-decoration-none">Beranda</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/campaign" class="text-muted text-decoration-none">Kampanye</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/about" class="text-muted text-decoration-none">Tentang</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/contact" class="text-muted text-decoration-none">Kontak</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5>Informasi</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= BASE_URL ?>/privacy" class="text-muted text-decoration-none">Privasi</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/terms" class="text-muted text-decoration-none">Ketentuan</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/faq" class="text-muted text-decoration-none">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-4">
                    <h5>Kontak</h5>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> <?= defined('CONTACT_EMAIL') ? CONTACT_EMAIL : 'info@donatehub.com' ?></li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i> <?= defined('CONTACT_PHONE') ? CONTACT_PHONE : '+62 123 456 7890' ?></li>
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> Jakarta, Indonesia</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="bg-darker py-3">
            <div class="container text-center">
                <p class="mb-0 text-muted">&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Set global variables for Javascript
        const BASE_URL = '<?= BASE_URL ?>';
    </script>
    <script src="<?= BASE_URL ?>/public/js/main.js"></script>
    
    <?php if (isset($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
</body>
</html> 