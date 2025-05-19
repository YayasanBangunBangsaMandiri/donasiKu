<?php require_once BASEPATH . '/app/Views/partials/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <img src="<?= BASE_URL ?>/public/img/500.svg" alt="500 Server Error" class="img-fluid mb-4" style="max-height: 300px;">
            <h1 class="display-4 fw-bold text-danger">500</h1>
            <h2 class="mb-4">Terjadi Kesalahan</h2>
            <p class="lead mb-5">Maaf, terjadi kesalahan pada server kami. Tim teknis kami telah diberitahu dan sedang menyelesaikan masalah ini.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="<?= BASE_URL ?>" class="btn btn-primary px-4 py-2">Kembali ke Beranda</a>
                <a href="<?= BASE_URL ?>/contact" class="btn btn-outline-secondary px-4 py-2">Hubungi Kami</a>
            </div>
            
            <?php if (isset($exception) && defined('DEBUG_MODE') && DEBUG_MODE): ?>
            <div class="alert alert-danger mt-5 text-start">
                <h4 class="alert-heading">Detail Error:</h4>
                <p><?= $exception->getMessage() ?></p>
                <hr>
                <p class="mb-0">File: <?= $exception->getFile() ?> on line <?= $exception->getLine() ?></p>
                <pre class="mt-3"><?= $exception->getTraceAsString() ?></pre>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once BASEPATH . '/app/Views/partials/footer.php'; ?> 