<?php
// Mulai output buffering
ob_start();
?>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>" class="text-decoration-none">Beranda</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tentang Kami</li>
        </ol>
    </nav>
    
    <!-- Hero Section -->
    <div class="row align-items-center mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <h1 class="mb-4">Tentang DonateHub</h1>
            <p class="lead mb-4">DonateHub adalah platform donasi online yang menghubungkan para donatur dengan berbagai kampanye sosial yang membutuhkan bantuan.</p>
            <p class="mb-4">Kami berkomitmen untuk menyediakan platform yang aman, transparan, dan mudah digunakan bagi semua orang yang ingin berbagi kebaikan. Dengan teknologi yang canggih dan sistem pembayaran yang terintegrasi, kami memastikan setiap donasi sampai ke tangan yang tepat.</p>
            <div class="d-flex gap-3">
                <div class="text-center">
                    <h3 class="fw-bold text-primary mb-2">100+</h3>
                    <p class="text-muted">Kampanye Aktif</p>
                </div>
                <div class="text-center">
                    <h3 class="fw-bold text-primary mb-2">10K+</h3>
                    <p class="text-muted">Donatur</p>
                </div>
                <div class="text-center">
                    <h3 class="fw-bold text-primary mb-2">Rp 1M+</h3>
                    <p class="text-muted">Donasi Tersalurkan</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <img src="<?= BASE_URL ?>/public/img/about-hero.jpg" alt="Tentang DonateHub" class="img-fluid rounded shadow">
        </div>
    </div>
    
    <!-- Misi dan Visi -->
    <div class="row mb-5">
        <div class="col-md-6 mb-4 mb-md-0">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="card-title mb-4">Misi Kami</h2>
                    <p class="card-text">Misi kami adalah menghubungkan orang-orang yang ingin membantu dengan mereka yang membutuhkan melalui platform digital yang aman, transparan, dan mudah digunakan.</p>
                    <p class="card-text">Kami berkomitmen untuk:</p>
                    <ul>
                        <li>Menyediakan platform donasi yang mudah diakses oleh semua orang</li>
                        <li>Memastikan transparansi dalam pengelolaan dana donasi</li>
                        <li>Memfasilitasi berbagai kampanye sosial yang berdampak positif bagi masyarakat</li>
                        <li>Membangun komunitas peduli yang saling membantu</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="card-title mb-4">Visi Kami</h2>
                    <p class="card-text">Visi kami adalah menjadi platform donasi terdepan di Indonesia yang menginspirasi perubahan sosial melalui teknologi.</p>
                    <p class="card-text">Kami percaya bahwa:</p>
                    <ul>
                        <li>Setiap orang memiliki potensi untuk membuat perubahan positif</li>
                        <li>Teknologi dapat memudahkan tindakan kemanusiaan</li>
                        <li>Transparansi adalah kunci kepercayaan dalam berdonasi</li>
                        <li>Kolaborasi antar individu dapat mengatasi masalah sosial yang kompleks</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tim Kami -->
    <div class="mb-5">
        <h2 class="text-center mb-5">Tim Kami</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="team-member">
                    <img src="<?= BASE_URL ?>/public/img/team-1.jpg" alt="CEO" class="rounded-circle mb-3 shadow">
                    <h4>Budi Santoso</h4>
                    <p class="text-primary mb-2">CEO & Founder</p>
                    <p class="text-muted small px-4">Berpengalaman lebih dari 10 tahun di bidang teknologi dan sosial. Budi memiliki visi untuk mendemokratisasi donasi melalui teknologi.</p>
                    <div class="social-links mt-3">
                        <a href="#" class="text-secondary me-2"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-secondary me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-secondary"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="team-member">
                    <img src="<?= BASE_URL ?>/public/img/team-2.jpg" alt="CTO" class="rounded-circle mb-3 shadow">
                    <h4>Siti Rahayu</h4>
                    <p class="text-primary mb-2">CTO</p>
                    <p class="text-muted small px-4">Ahli teknologi dengan latar belakang di bidang keamanan cyber. Siti memastikan platform kami aman dan terpercaya.</p>
                    <div class="social-links mt-3">
                        <a href="#" class="text-secondary me-2"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-secondary me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-secondary"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="team-member">
                    <img src="<?= BASE_URL ?>/public/img/team-3.jpg" alt="COO" class="rounded-circle mb-3 shadow">
                    <h4>Ahmad Hidayat</h4>
                    <p class="text-primary mb-2">COO</p>
                    <p class="text-muted small px-4">Berpengalaman dalam manajemen operasional di berbagai perusahaan teknologi. Ahmad memastikan semua operasi berjalan lancar.</p>
                    <div class="social-links mt-3">
                        <a href="#" class="text-secondary me-2"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-secondary me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-secondary"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bagaimana Kami Bekerja -->
    <div class="mb-5">
        <h2 class="text-center mb-4">Bagaimana Kami Bekerja</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100 text-center">
                    <div class="card-body p-4">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <i class="fas fa-hand-holding-heart fa-2x"></i>
                        </div>
                        <h4>1. Pilih Kampanye</h4>
                        <p class="text-muted">Kami memverifikasi setiap kampanye yang terdaftar untuk memastikan keaslian dan integritas. Anda dapat memilih kampanye yang sesuai dengan nilai-nilai dan kepedulian Anda.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100 text-center">
                    <div class="card-body p-4">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                        <h4>2. Berikan Donasi</h4>
                        <p class="text-muted">Kami menyediakan berbagai metode pembayaran yang aman dan terpercaya. Setiap transaksi dienkripsi dan dilindungi dengan teknologi keamanan terkini.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100 text-center">
                    <div class="card-body p-4">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <i class="fas fa-clipboard-check fa-2x"></i>
                        </div>
                        <h4>3. Lihat Dampaknya</h4>
                        <p class="text-muted">Kami memberikan update rutin tentang perkembangan kampanye dan penggunaan dana. Anda dapat melihat bagaimana donasi Anda membantu orang lain.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Testimoni -->
    <div class="mb-5">
        <h2 class="text-center mb-4">Kata Mereka Tentang Kami</h2>
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3 text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="card-text mb-4">"Platform yang sangat user-friendly dan transparan. Saya bisa melihat dengan jelas kemana donasi saya diarahkan dan bagaimana dampaknya."</p>
                        <div class="d-flex align-items-center">
                            <img src="<?= BASE_URL ?>/public/img/testimonial-1.jpg" alt="Testimonial" class="rounded-circle" width="50" height="50">
                            <div class="ms-3">
                                <h6 class="mb-0">Dewi Rahma</h6>
                                <small class="text-muted">Donatur</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3 text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="card-text mb-4">"DonateHub telah membantu kampanye kami mencapai target dalam waktu singkat. Proses verifikasi yang profesional membuat kami dan para donatur merasa aman."</p>
                        <div class="d-flex align-items-center">
                            <img src="<?= BASE_URL ?>/public/img/testimonial-2.jpg" alt="Testimonial" class="rounded-circle" width="50" height="50">
                            <div class="ms-3">
                                <h6 class="mb-0">Rudi Hartono</h6>
                                <small class="text-muted">Penggalang Dana</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3 text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="card-text mb-4">"Melalui DonateHub, kami dapat menyalurkan bantuan ke daerah terdampak bencana dengan cepat dan tepat. Dashboard yang informatif membuat pengelolaan dana menjadi mudah."</p>
                        <div class="d-flex align-items-center">
                            <img src="<?= BASE_URL ?>/public/img/testimonial-3.jpg" alt="Testimonial" class="rounded-circle" width="50" height="50">
                            <div class="ms-3">
                                <h6 class="mb-0">Rina Wijaya</h6>
                                <small class="text-muted">Mitra LSM</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- CTA -->
    <div class="card border-0 shadow-sm bg-light">
        <div class="card-body p-5 text-center">
            <h2 class="mb-3">Bergabunglah dengan Kami</h2>
            <p class="lead mb-4">Jadilah bagian dari perubahan positif. Mulai donasi atau galang dana sekarang!</p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="<?= BASE_URL ?>/campaign" class="btn btn-primary btn-lg">Donasi Sekarang</a>
                <a href="<?= BASE_URL ?>/auth/register" class="btn btn-outline-primary btn-lg">Buat Kampanye</a>
            </div>
        </div>
    </div>
</div>

<?php
// Ambil konten dari buffer
$content = ob_get_clean();

// Render layout dengan konten
include 'app/Views/layouts/main.php';
?> 