<?php
namespace App\Controllers;

/**
 * Controller untuk halaman utama
 */
class HomeController extends Controller {
    /**
     * Menampilkan halaman utama
     * 
     * @return void
     */
    public function index() {
        // Ambil campaign yang aktif untuk ditampilkan di halaman utama
        $campaigns = $this->db->fetchAll(
            "SELECT * FROM campaigns WHERE status = 'active' ORDER BY created_at DESC LIMIT 6"
        );
        
        // Ambil total donasi yang sudah terkumpul
        $totalDonation = $this->db->fetchColumn(
            "SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'success'"
        );
        
        // Ambil jumlah donatur
        $totalDonors = $this->db->fetchColumn(
            "SELECT COUNT(DISTINCT email) FROM donations WHERE status = 'success'"
        );
        
        // Render view dengan data
        $this->view('home/index', [
            'title' => APP_NAME . ' - Platform Donasi Online',
            'campaigns' => $campaigns,
            'totalDonation' => $totalDonation,
            'totalDonors' => $totalDonors
        ]);
    }
    
    /**
     * Menampilkan halaman tentang kami
     * 
     * @return void
     */
    public function about() {
        $this->view('home/about', [
            'title' => 'Tentang Kami - ' . APP_NAME
        ]);
    }
    
    /**
     * Menampilkan halaman kontak
     * 
     * @return void
     */
    public function contact() {
        $this->view('home/contact', [
            'title' => 'Hubungi Kami - ' . APP_NAME
        ]);
    }
    
    /**
     * Memproses form kontak
     * 
     * @return void
     */
    public function submitContact() {
        // Validasi input
        $errors = $this->validate($_POST, [
            'name' => 'required|max:100',
            'email' => 'required|email',
            'subject' => 'required|max:200',
            'message' => 'required'
        ]);
        
        if (!empty($errors)) {
            // Jika ada error, kembalikan ke form dengan pesan error
            $this->setFlash('errors', $errors);
            $this->setFlash('old', $_POST);
            $this->redirect('contact');
            return;
        }
        
        // Simpan pesan kontak ke database
        $this->db->query(
            "INSERT INTO messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())",
            [
                $_POST['name'],
                $_POST['email'],
                $_POST['subject'],
                $_POST['message']
            ]
        );
        
        // Kirim email notifikasi ke admin (implementasi sebenarnya akan menggunakan library email)
        // TODO: Implementasi pengiriman email
        
        // Set flash message sukses
        $this->setFlash('success', 'Pesan Anda telah terkirim. Kami akan menghubungi Anda segera.');
        
        // Redirect kembali ke halaman kontak
        $this->redirect('contact');
    }
} 