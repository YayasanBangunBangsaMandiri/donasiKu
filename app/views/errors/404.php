<?php require_once BASEPATH . '/app/views/partials/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <img src="<?= BASE_URL ?>/public/img/404.svg" alt="404 Not Found" class="img-fluid mb-4" style="max-height: 300px;">
            <h1 class="display-4 fw-bold text-danger">404</h1>
            <h2 class="mb-4">Halaman Tidak Ditemukan</h2>
            <p class="lead mb-5">Maaf, halaman yang Anda cari tidak ditemukan atau telah dipindahkan.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="<?= BASE_URL ?>" class="btn btn-primary px-4 py-2">Kembali ke Beranda</a>
                <a href="<?= BASE_URL ?>/contact" class="btn btn-outline-secondary px-4 py-2">Hubungi Kami</a>
            </div>
        </div>
    </div>
</div>

<?php require_once BASEPATH . '/app/views/partials/footer.php'; ?> 