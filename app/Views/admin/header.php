<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title . ' - ' . APP_NAME . ' Admin' : APP_NAME . ' Admin' ?></title>
    
    <!-- Favicon -->
    <!-- <link rel="icon" href="<?= BASE_URL ?>/public/img/favicon.ico" type="image/x-icon"> -->
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body class="admin">
    <!-- Header Navigation -->
    <header class="admin-header">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="<?= BASE_URL ?>/admin/dashboard">
                    <!-- <img src="<?= BASE_URL ?>/public/img/logo.png" alt="<?= APP_NAME ?>" height="36"> -->
                    <?= APP_NAME ?> Admin
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin" 
                        aria-controls="navbarAdmin" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarAdmin">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/dashboard') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/admin/dashboard">
                                <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/campaigns') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/admin/campaigns">
                                <i class="fas fa-hand-holding-heart me-1"></i> Kampanye
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/donations') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/admin/donations">
                                <i class="fas fa-donate me-1"></i> Donasi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/admin/settings">
                                <i class="fas fa-cog me-1"></i> Pengaturan
                            </a>
                        </li>
                    </ul>
                    
                 
                </div>
            </div>
        </nav>
        <div class="dropdown">
                        <div class="user-profile" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                            <i class="fas fa-user"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="px-3 py-2 border-bottom">
                                <h6 class="m-0"><?= isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Admin' ?></h6>
                                <small class="text-muted"><?= isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : '' ?></small>
                            </li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/profile"><i class="fas fa-user-cog me-2"></i> Profil Saya</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/auth/logout"><i class="fas fa-sign-out-alt me-2"></i> Keluar</a></li>
                        </ul>
                    </div>
    </header>

    <!-- Main Content -->
    <main class="admin-content">
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i> <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i> <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <!-- Admin Content Container -->
        <div class="container-fluid p-0">
            <!-- Admin Content -->