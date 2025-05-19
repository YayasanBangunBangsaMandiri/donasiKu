<?php
namespace App\Controllers;

use App\Models\Donation;
use App\Models\Campaign;
use App\Helpers\MidtransHelper;

/**
 * Controller untuk donasi
 */
class DonationController extends Controller {
    private $donationModel;
    private $campaignModel;
    private $midtransHelper;
    
    public function __construct() {
        parent::__construct();
        $this->donationModel = new Donation();
        $this->campaignModel = new Campaign();
        $this->midtransHelper = new MidtransHelper();
    }
    
    /**
     * Halaman formulir donasi
     * 
     * @param string $slug Slug kampanye
     * @return void
     */
    public function form($slug) {
        // Mendapatkan data kampanye
        $campaign = $this->campaignModel->findBySlug($slug);
        
        if (!$campaign) {
            $this->redirect('campaign');
            return;
        }
        
        // Jika kampanye tidak aktif
        if ($campaign['status'] !== 'active') {
            $this->setFlash('error', 'Kampanye ini sedang tidak aktif.');
            $this->redirect('campaign');
            return;
        }
        
        // Jika kampanye sudah lewat tanggal akhir
        $endDate = new \DateTime($campaign['end_date']);
        $today = new \DateTime();
        
        if ($today > $endDate) {
            $this->setFlash('error', 'Kampanye ini sudah berakhir.');
            $this->redirect('campaign');
            return;
        }
        
        // Mendapatkan jumlah donasi preset
        $donationAmounts = DEFAULT_DONATION_AMOUNTS;
        if (!empty($campaign['donation_amounts'])) {
            $donationAmounts = json_decode($campaign['donation_amounts'], true);
        }
        
        // Mendapatkan panduan pembayaran
        $paymentGuides = $this->db->fetchAll(
            "SELECT * FROM payment_guides WHERE is_active = 1 ORDER BY payment_method, payment_channel"
        );
        
        // Render view donasi
        $this->view('donation/form', [
            'title' => 'Donasi untuk ' . $campaign['title'],
            'campaign' => $campaign,
            'donationAmounts' => $donationAmounts,
            'allowCustomAmount' => !empty($campaign['allow_custom_amount']) ? $campaign['allow_custom_amount'] : ALLOW_CUSTOM_AMOUNT,
            'minAmount' => MIN_DONATION_AMOUNT,
            'maxAmount' => MAX_DONATION_AMOUNT,
            'paymentGuides' => $paymentGuides
        ]);
    }
    
    /**
     * Proses donasi
     * 
     * @return void
     */
    public function process() {
        // Validasi input
        $errors = $this->validate($_POST, [
            'campaign_id' => 'required|numeric',
            'name' => 'required|max:100',
            'email' => 'required|email',
            'amount' => 'required|numeric|min_value:' . MIN_DONATION_AMOUNT . '|max_value:' . MAX_DONATION_AMOUNT,
            'payment_method' => 'required'
        ]);
        
        // Menangani validasi phone jika ada
        if (!empty($_POST['phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
            if (strlen($phone) < 10 || strlen($phone) > 15) {
                $errors['phone'][] = 'Nomor telepon harus antara 10-15 digit.';
            }
        }
        
        if (!empty($errors)) {
            $this->setFlash('errors', $errors);
            $this->setFlash('old', $_POST);
            $this->redirect('donation/form/' . $_POST['campaign_slug']);
            return;
        }
        
        // Mendapatkan data kampanye
        $campaign = $this->campaignModel->find($_POST['campaign_id']);
        
        if (!$campaign) {
            $this->setFlash('error', 'Kampanye tidak ditemukan.');
            $this->redirect('campaign');
            return;
        }
        
        // Jika kampanye tidak aktif
        if ($campaign['status'] !== 'active') {
            $this->setFlash('error', 'Kampanye ini sedang tidak aktif.');
            $this->redirect('campaign');
            return;
        }
        
        // Jika kampanye sudah lewat tanggal akhir
        $endDate = new \DateTime($campaign['end_date']);
        $today = new \DateTime();
        
        if ($today > $endDate) {
            $this->setFlash('error', 'Kampanye ini sudah berakhir.');
            $this->redirect('campaign');
            return;
        }
        
        // Data donasi
        $donationData = [
            'campaign_id' => $_POST['campaign_id'],
            'user_id' => isset($_SESSION['user']) ? $_SESSION['user']['id'] : null,
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'] ?? '',
            'amount' => $_POST['amount'],
            'payment_method' => $_POST['payment_method'],
            'message' => $_POST['message'] ?? '',
            'is_anonymous' => isset($_POST['is_anonymous']) ? 1 : 0,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Simpan donasi ke database
        $donationId = $this->donationModel->create($donationData);
        
        if (!$donationId) {
            $this->setFlash('error', 'Gagal membuat donasi. Silakan coba lagi.');
            $this->redirect('donation/form/' . $campaign['slug']);
            return;
        }
        
        // Dapatkan data donasi lengkap
        $donation = $this->donationModel->find($donationId);
        
        // Buat transaksi Midtrans
        $transaction = $this->midtransHelper->createTransaction($donation, $campaign);
        
        if (!$transaction['success']) {
            $this->setFlash('error', 'Gagal membuat transaksi pembayaran: ' . $transaction['message']);
            $this->redirect('donation/form/' . $campaign['slug']);
            return;
        }
        
        // Redirect ke halaman pembayaran Midtrans
        header('Location: ' . $transaction['redirect_url']);
        exit;
    }
    
    /**
     * Callback setelah pembayaran selesai
     * 
     * @param int $id ID donasi
     * @return void
     */
    public function finish($id) {
        $donation = $this->donationModel->find($id);
        
        if (!$donation) {
            $this->setFlash('error', 'Donasi tidak ditemukan.');
            $this->redirect('campaign');
            return;
        }
        
        // Periksa status transaksi di Midtrans
        $transaction = $this->midtransHelper->checkTransaction($donation['order_id']);
        
        // Dapatkan data kampanye
        $campaign = $this->campaignModel->find($donation['campaign_id']);
        
        // Render view sukses donasi
        $this->view('donation/finish', [
            'title' => 'Terima Kasih atas Donasi Anda',
            'donation' => $donation,
            'campaign' => $campaign,
            'transaction' => $transaction
        ]);
    }
    
    /**
     * Callback jika pembayaran belum selesai
     * 
     * @param int $id ID donasi
     * @return void
     */
    public function unfinish($id) {
        $donation = $this->donationModel->find($id);
        
        if (!$donation) {
            $this->setFlash('error', 'Donasi tidak ditemukan.');
            $this->redirect('campaign');
            return;
        }
        
        // Dapatkan data kampanye
        $campaign = $this->campaignModel->find($donation['campaign_id']);
        
        // Render view pembayaran belum selesai
        $this->view('donation/unfinish', [
            'title' => 'Pembayaran Belum Selesai',
            'donation' => $donation,
            'campaign' => $campaign
        ]);
    }
    
    /**
     * Callback jika terjadi error pada pembayaran
     * 
     * @param int $id ID donasi
     * @return void
     */
    public function error($id) {
        $donation = $this->donationModel->find($id);
        
        if (!$donation) {
            $this->setFlash('error', 'Donasi tidak ditemukan.');
            $this->redirect('campaign');
            return;
        }
        
        // Dapatkan data kampanye
        $campaign = $this->campaignModel->find($donation['campaign_id']);
        
        // Render view error donasi
        $this->view('donation/error', [
            'title' => 'Error Pembayaran',
            'donation' => $donation,
            'campaign' => $campaign
        ]);
    }
    
    /**
     * Endpoint untuk notifikasi webhook dari Midtrans
     * 
     * @return void
     */
    public function notification() {
        // Ambil data notifikasi dari Midtrans
        $notification = json_decode(file_get_contents('php://input'), true);
        
        // Log notifikasi untuk debugging
        $logFile = BASEPATH . '/logs/midtrans_notification_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, date('Y-m-d H:i:s') . ': ' . json_encode($notification) . "\n", FILE_APPEND);
        
        // Proses notifikasi
        $result = $this->midtransHelper->handleNotification($notification);
        
        // Kirim response ke Midtrans
        header('Content-Type: application/json');
        echo json_encode(['status' => $result['success'] ? 'success' : 'error', 'message' => $result['message']]);
        exit;
    }
    
    /**
     * Mendapatkan panduan pembayaran
     * 
     * @return void
     */
    public function paymentGuide() {
        // Validasi input
        if (!isset($_GET['method']) || empty($_GET['method']) || !isset($_GET['channel']) || empty($_GET['channel'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap']);
            exit;
        }
        
        // Mendapatkan panduan pembayaran
        $guide = $this->db->fetch(
            "SELECT * FROM payment_guides WHERE payment_method = ? AND payment_channel = ? AND is_active = 1",
            [$_GET['method'], $_GET['channel']]
        );
        
        if (!$guide) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Panduan pembayaran tidak ditemukan']);
            exit;
        }
        
        // Konversi steps dari JSON ke array
        $guide['steps'] = json_decode($guide['steps'], true);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $guide]);
        exit;
    }
    
    /**
     * Menampilkan riwayat donasi untuk user yang login
     * 
     * @return void
     */
    public function history() {
        // Cek apakah user sudah login
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }
        
        // Mendapatkan riwayat donasi
        $donations = $this->donationModel->getAllByUserId($_SESSION['user']['id']);
        
        // Render view riwayat donasi
        $this->view('donation/history', [
            'title' => 'Riwayat Donasi',
            'donations' => $donations
        ]);
    }
    
    /**
     * Menampilkan detail donasi
     * 
     * @param int $id ID donasi
     * @return void
     */
    public function detail($id) {
        // Cek apakah user sudah login
        if (!isset($_SESSION['user'])) {
            $this->redirect('login');
            return;
        }
        
        // Mendapatkan data donasi
        $donation = $this->donationModel->find($id);
        
        if (!$donation) {
            $this->setFlash('error', 'Donasi tidak ditemukan.');
            $this->redirect('donation/history');
            return;
        }
        
        // Cek apakah donasi milik user yang login
        if ($donation['user_id'] != $_SESSION['user']['id'] && $_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'super_admin') {
            $this->setFlash('error', 'Anda tidak memiliki akses untuk melihat donasi ini.');
            $this->redirect('donation/history');
            return;
        }
        
        // Dapatkan data kampanye
        $campaign = $this->campaignModel->find($donation['campaign_id']);
        
        // Dapatkan panduan pembayaran
        $paymentGuide = null;
        if ($donation['status'] == 'pending' && !empty($donation['payment_method']) && !empty($donation['payment_channel'])) {
            $paymentGuide = $this->db->fetch(
                "SELECT * FROM payment_guides WHERE payment_method = ? AND payment_channel = ? AND is_active = 1",
                [$donation['payment_method'], $donation['payment_channel']]
            );
            
            if ($paymentGuide) {
                $paymentGuide['steps'] = json_decode($paymentGuide['steps'], true);
            }
        }
        
        // Render view detail donasi
        $this->view('donation/detail', [
            'title' => 'Detail Donasi',
            'donation' => $donation,
            'campaign' => $campaign,
            'paymentGuide' => $paymentGuide
        ]);
    }
} 