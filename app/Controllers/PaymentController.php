<?php
namespace App\Controllers;

use App\Models\Donation;
use App\Models\Campaign;
use App\Helpers\NotificationHelper;

/**
 * Controller untuk mengelola pembayaran dan notifikasi
 */
class PaymentController extends Controller {
    private $donationModel;
    private $campaignModel;
    private $notificationHelper;
    
    public function __construct() {
        parent::__construct();
        $this->donationModel = new Donation();
        $this->campaignModel = new Campaign();
        $this->notificationHelper = new NotificationHelper();
    }
    
    /**
     * Endpoint untuk menerima webhook dari Midtrans
     * 
     * @return void
     */
    public function webhook() {
        // Verifikasi bahwa request berasal dari Midtrans
        $notificationBody = file_get_contents('php://input');
        
        // Log notifikasi untuk debug
        error_log('Midtrans Webhook: ' . $notificationBody);
        
        try {
            $notification = json_decode($notificationBody, true);
            
            if (!$notification || !isset($notification['order_id'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid notification data']);
                exit;
            }
            
            // Proses notifikasi
            $this->processNotification($notification);
            
            // Respon ke Midtrans
            http_response_code(200);
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Memproses notifikasi pembayaran
     * 
     * @param array $notification Data notifikasi
     * @return void
     */
    private function processNotification($notification) {
        // Cari donasi berdasarkan order_id
        $donation = $this->donationModel->findByOrderId($notification['order_id']);
        
        if (!$donation) {
            throw new \Exception('Donation not found for order_id: ' . $notification['order_id']);
        }
        
        // Update status donasi
        $status = $this->mapMidtransStatus($notification['transaction_status'], $notification['fraud_status'] ?? null);
        
        // Update data donasi
        $updateData = [
            'status' => $status,
            'payment_method' => $notification['payment_type'] ?? $donation['payment_method'],
            'payment_data' => json_encode($notification)
        ];
        
        // Jika pembayaran berhasil, catat waktu pembayaran
        if ($status === 'success') {
            $updateData['paid_at'] = date('Y-m-d H:i:s');
            $updateData['settlement_time'] = $notification['settlement_time'] ?? date('Y-m-d H:i:s');
        }
        
        // Update donasi
        $this->donationModel->update($donation['id'], $updateData);
        
        // Jika status berubah, kirim notifikasi
        if ($donation['status'] !== $status) {
            $this->sendNotifications($donation['id'], $status);
        }
        
        // Jika donasi berhasil, update jumlah donasi pada kampanye
        if ($status === 'success') {
            $this->campaignModel->updateAmount($donation['campaign_id'], $donation['amount']);
        }
    }
    
    /**
     * Mengirim notifikasi ke donatur
     * 
     * @param int $donationId ID donasi
     * @param string $status Status baru
     * @return void
     */
    private function sendNotifications($donationId, $status) {
        // Dapatkan data donasi dan kampanye
        $donation = $this->donationModel->findWithCampaign($donationId);
        
        if (!$donation) {
            return;
        }
        
        // Kirim notifikasi email
        $this->notificationHelper->sendEmailNotification($donation, $donation['campaign']);
        
        // Kirim notifikasi WhatsApp jika nomor telepon tersedia
        if (!empty($donation['phone'])) {
            $this->notificationHelper->sendWhatsAppNotification($donation, $donation['campaign']);
        }
        
        // Tambahkan notifikasi ke database
        $this->addNotificationToDatabase($donation, $status);
    }
    
    /**
     * Menambahkan notifikasi ke database
     * 
     * @param array $donation Data donasi
     * @param string $status Status baru
     * @return void
     */
    private function addNotificationToDatabase($donation, $status) {
        // Buat pesan notifikasi berdasarkan status
        $message = '';
        $type = '';
        
        switch ($status) {
            case 'success':
                $message = 'Donasi Anda sebesar Rp ' . number_format($donation['amount'], 0, ',', '.') . ' untuk kampanye "' . $donation['campaign']['title'] . '" telah berhasil.';
                $type = 'success';
                break;
            case 'pending':
                $message = 'Menunggu pembayaran untuk donasi Anda sebesar Rp ' . number_format($donation['amount'], 0, ',', '.') . ' untuk kampanye "' . $donation['campaign']['title'] . '".';
                $type = 'warning';
                break;
            case 'failed':
                $message = 'Pembayaran donasi Anda sebesar Rp ' . number_format($donation['amount'], 0, ',', '.') . ' untuk kampanye "' . $donation['campaign']['title'] . '" gagal.';
                $type = 'danger';
                break;
            default:
                $message = 'Status donasi Anda untuk kampanye "' . $donation['campaign']['title'] . '" telah diperbarui.';
                $type = 'info';
        }
        
        // Insert ke tabel notifications
        $this->db->query(
            "INSERT INTO notifications (user_id, donation_id, campaign_id, message, type, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
            [$donation['user_id'] ?? null, $donation['id'], $donation['campaign_id'], $message, $type]
        );
    }
    
    /**
     * Memetakan status Midtrans ke status aplikasi
     * 
     * @param string $transactionStatus Status transaksi
     * @param string|null $fraudStatus Status fraud
     * @return string
     */
    private function mapMidtransStatus($transactionStatus, $fraudStatus = null) {
        switch ($transactionStatus) {
            case 'capture':
                return ($fraudStatus == 'challenge') ? 'pending' : 'success';
            case 'settlement':
                return 'success';
            case 'pending':
                return 'pending';
            case 'deny':
            case 'cancel':
            case 'expire':
                return 'failed';
            default:
                return 'pending';
        }
    }
    
    /**
     * Halaman sukses setelah pembayaran berhasil
     * 
     * @param int $id ID donasi
     * @return void
     */
    public function success($id) {
        $donation = $this->donationModel->getWithDetails($id);
        
        if (!$donation) {
            $this->redirect('donation/failed');
            return;
        }
        
        $this->view('donation/success', [
            'title' => 'Pembayaran Berhasil - ' . APP_NAME,
            'donation' => $donation
        ]);
    }
    
    /**
     * Halaman pending setelah pembayaran pending
     * 
     * @param int $id ID donasi
     * @return void
     */
    public function pending($id) {
        $donation = $this->donationModel->getWithDetails($id);
        
        if (!$donation) {
            $this->redirect('donation/failed');
            return;
        }
        
        $this->view('donation/pending', [
            'title' => 'Menunggu Pembayaran - ' . APP_NAME,
            'donation' => $donation
        ]);
    }
    
    /**
     * Halaman gagal setelah pembayaran gagal
     * 
     * @param int $id ID donasi
     * @return void
     */
    public function failed($id = null) {
        $donation = null;
        
        if ($id) {
            $donation = $this->donationModel->getWithDetails($id);
        }
        
        $this->view('donation/failed', [
            'title' => 'Pembayaran Gagal - ' . APP_NAME,
            'donation' => $donation
        ]);
    }
} 