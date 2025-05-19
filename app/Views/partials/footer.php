    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <!-- <img src="<?= BASE_URL ?>/public/img/logo-white.png" alt="<?= APP_NAME ?>" class="mb-3" height="40"> -->
                    <h4 class="text-white mb-3"><?= APP_NAME ?></h4>
                    <p>Platform donasi online yang menghubungkan para donatur dengan berbagai kampanye sosial.</p>
                </div>
                
                <!-- Navigasi -->
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-3">Navigasi</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= BASE_URL ?>" class="text-white">Beranda</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/campaign" class="text-white">Kampanye</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/about" class="text-white">Tentang Kami</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/contact" class="text-white">Kontak</a></li>
                    </ul>
                </div>
                
                <!-- Kontak -->
                <div class="col-md-4">
                    <h5 class="mb-3">Kontak</h5>
                    <p class="mb-2"><i class="fas fa-envelope me-2"></i> info@donatehub.com</p>
                    <p class="mb-2"><i class="fas fa-phone me-2"></i> +62 123 456 7890</p>
                    <p class="mb-4"><i class="fas fa-map-marker-alt me-2"></i> Jakarta, Indonesia</p>
                    
                    <div class="d-flex">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <!-- Copyright dan Kebijakan -->
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-md-0 text-muted">&copy; <?= date('Y') ?> <?= APP_NAME ?>. Hak Cipta Dilindungi.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="<?= BASE_URL ?>/terms" class="text-muted me-3">Syarat & Ketentuan</a>
                    <a href="<?= BASE_URL ?>/privacy" class="text-muted">Kebijakan Privasi</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>/public/js/script.js"></script>
</body>
</html> 