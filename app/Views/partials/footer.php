    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-4 mt-5">
        <div class="container">
            <div class="row">
                <!-- Logo dan Deskripsi -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <img src="<?= BASE_URL ?>/public/img/logo-white.png" alt="<?= APP_NAME ?>" class="mb-3" height="40">
                    <p class="text-muted">Platform donasi online terpercaya yang menghubungkan donatur dengan berbagai kampanye sosial, kesehatan, pendidikan, dan kemanusiaan.</p>
                    <div class="mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <!-- Tautan Cepat -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h5>Tautan Cepat</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= BASE_URL ?>" class="text-muted">Beranda</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/campaign" class="text-muted">Kampanye</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/about" class="text-muted">Tentang Kami</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/contact" class="text-muted">Hubungi Kami</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/auth/register" class="text-muted">Daftar</a></li>
                        <li><a href="<?= BASE_URL ?>/auth/login" class="text-muted">Masuk</a></li>
                    </ul>
                </div>
                
                <!-- Kategori -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h5>Kategori</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= BASE_URL ?>/campaign/category/kesehatan" class="text-muted">Kesehatan</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/campaign/category/pendidikan" class="text-muted">Pendidikan</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/campaign/category/bencana-alam" class="text-muted">Bencana Alam</a></li>
                        <li class="mb-2"><a href="<?= BASE_URL ?>/campaign/category/sosial" class="text-muted">Sosial</a></li>
                        <li><a href="<?= BASE_URL ?>/campaign/category/lingkungan" class="text-muted">Lingkungan</a></li>
                    </ul>
                </div>
                
                <!-- Kontak -->
                <div class="col-lg-3 col-md-6">
                    <h5>Hubungi Kami</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2 text-muted"><i class="fas fa-map-marker-alt me-2"></i> Jl. Contoh No. 123, Jakarta</li>
                        <li class="mb-2 text-muted"><i class="fas fa-phone me-2"></i> (021) 1234-5678</li>
                        <li class="mb-2 text-muted"><i class="fas fa-envelope me-2"></i> info@donatehub.com</li>
                        <li class="mb-2 text-muted"><i class="fas fa-clock me-2"></i> Senin - Jumat, 09:00 - 17:00</li>
                    </ul>
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