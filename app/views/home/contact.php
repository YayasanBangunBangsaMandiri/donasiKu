<?php
// Mulai output buffering
ob_start();
?>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>" class="text-decoration-none">Beranda</a></li>
            <li class="breadcrumb-item active" aria-current="page">Hubungi Kami</li>
        </ol>
    </nav>
    
    <div class="row mb-5">
        <div class="col-md-6 mb-4 mb-md-0">
            <h1 class="mb-4">Hubungi Kami</h1>
            <p class="lead mb-4">Kami senang mendengar dari Anda. Hubungi kami untuk pertanyaan, saran, atau dukungan.</p>
            
            <?php if (isset($flash['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $flash['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($flash['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $flash['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form action="<?= BASE_URL ?>/home/submitContact" method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="name" name="name" required value="<?= isset($flash['old']['name']) ? $flash['old']['name'] : '' ?>">
                    <?php if (isset($flash['errors']['name'])): ?>
                        <div class="text-danger"><?= $flash['errors']['name'][0] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required value="<?= isset($flash['old']['email']) ? $flash['old']['email'] : '' ?>">
                    <?php if (isset($flash['errors']['email'])): ?>
                        <div class="text-danger"><?= $flash['errors']['email'][0] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label for="subject" class="form-label">Subjek</label>
                    <input type="text" class="form-control" id="subject" name="subject" required value="<?= isset($flash['old']['subject']) ? $flash['old']['subject'] : '' ?>">
                    <?php if (isset($flash['errors']['subject'])): ?>
                        <div class="text-danger"><?= $flash['errors']['subject'][0] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4">
                    <label for="message" class="form-label">Pesan</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required><?= isset($flash['old']['message']) ? $flash['old']['message'] : '' ?></textarea>
                    <?php if (isset($flash['errors']['message'])): ?>
                        <div class="text-danger"><?= $flash['errors']['message'][0] ?></div>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn btn-primary">Kirim Pesan</button>
            </form>
        </div>
        
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h3 class="mb-4">Informasi Kontak</h3>
                    
                    <div class="contact-info-item mb-4">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h5>Alamat Kantor</h5>
                            <p class="mb-0">Jl. Contoh No. 123, Jakarta Selatan<br>Indonesia 12345</p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item mb-4">
                        <i class="fas fa-phone-alt"></i>
                        <div>
                            <h5>Telepon</h5>
                            <p class="mb-0">+6281234567890</p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item mb-4">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h5>Email</h5>
                            <p class="mb-0">contact@donatehub.com</p>
                        </div>
                    </div>
                    
                    <div class="contact-info-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h5>Jam Operasional</h5>
                            <p class="mb-0">Senin - Jumat: 09:00 - 17:00<br>Sabtu: 09:00 - 14:00<br>Minggu: Tutup</p>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h5 class="mb-3">Ikuti Kami</h5>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-decoration-none text-primary fs-4"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-decoration-none text-primary fs-4"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-decoration-none text-primary fs-4"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-decoration-none text-primary fs-4"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Google Maps -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-body p-0">
            <div class="ratio ratio-16x9">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.0662381826784!2d106.82949131536967!3d-6.2502232634757775!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f3e502850fb3%3A0x1c815859ce97557a!2sMonumen%20Nasional!5e0!3m2!1sid!2sid!4v1650000000000!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
    
    <!-- FAQ Section -->
    <div class="mb-5">
        <h2 class="mb-4">Pertanyaan Umum</h2>
        
        <div class="accordion" id="accordionFAQ">
            <div class="accordion-item border-0 mb-3 shadow-sm">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        Bagaimana cara melakukan donasi?
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionFAQ">
                    <div class="accordion-body">
                        Untuk melakukan donasi, Anda dapat mengikuti langkah-langkah berikut: Pilih kampanye yang ingin Anda dukung, klik tombol "Donasi Sekarang", isi formulir donasi dengan informasi yang diperlukan, pilih metode pembayaran, dan selesaikan transaksi. Anda akan menerima konfirmasi setelah donasi berhasil.
                    </div>
                </div>
            </div>
            
            <div class="accordion-item border-0 mb-3 shadow-sm">
                <h2 class="accordion-header" id="headingTwo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        Metode pembayaran apa saja yang tersedia?
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionFAQ">
                    <div class="accordion-body">
                        Kami menyediakan berbagai metode pembayaran termasuk transfer bank (virtual account), e-wallet (GoPay, OVO, ShopeePay), dan kartu kredit. Semua transaksi pembayaran diproses melalui Midtrans yang aman dan terpercaya.
                    </div>
                </div>
            </div>
            
            <div class="accordion-item border-0 mb-3 shadow-sm">
                <h2 class="accordion-header" id="headingThree">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                        Bagaimana cara membuat kampanye donasi?
                    </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionFAQ">
                    <div class="accordion-body">
                        Untuk membuat kampanye donasi, Anda perlu mendaftar atau masuk ke akun Anda. Setelah itu, navigasi ke dashboard pengguna, klik tombol "Buat Kampanye", isi formulir dengan informasi kampanye seperti judul, deskripsi, target donasi, dan gambar, kemudian kirimkan untuk ditinjau. Setelah disetujui, kampanye Anda akan muncul di platform kami.
                    </div>
                </div>
            </div>
            
            <div class="accordion-item border-0 shadow-sm">
                <h2 class="accordion-header" id="headingFour">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                        Apakah ada biaya administrasi untuk donasi?
                    </button>
                </h2>
                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionFAQ">
                    <div class="accordion-body">
                        Ya, kami mengenakan biaya administrasi sebesar 2,5% dari setiap donasi untuk menjalankan platform dan memastikan layanan terbaik bagi pengguna. Biaya ini sudah termasuk dalam jumlah donasi yang Anda berikan.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Ambil konten dari buffer
$content = ob_get_clean();

// Render layout dengan konten
include 'app/views/layouts/main.php';
?> 