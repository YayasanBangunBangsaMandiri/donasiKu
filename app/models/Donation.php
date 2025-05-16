<?php
namespace App\Models;

/**
 * Model untuk mengelola data donasi
 */
class Donation extends Model {
    protected $table = 'donations';
    protected $fillable = [
        'campaign_id', 'user_id', 'name', 'email', 'phone', 
        'amount', 'payment_method', 'payment_channel', 'status', 
        'message', 'is_anonymous', 'transaction_id', 'payment_data'
    ];
    
    /**
     * Membuat donasi baru
     * 
     * @param array $data Data donasi
     * @return array|false Data donasi dengan info pembayaran atau false jika gagal
     */
    public function createDonation($data) {
        // Validasi jumlah donasi
        if ($data['amount'] < MIN_DONATION_AMOUNT || $data['amount'] > MAX_DONATION_AMOUNT) {
            return false;
        }
        
        // Set status awal
        $data['status'] = 'pending';
        
        // Set waktu pembuatan
        $data['created_at'] = date('Y-m-d H:i:s');
        
        // Generate transaction ID
        $data['transaction_id'] = $this->generateTransactionId();
        
        // Simpan donasi ke database
        $donationId = $this->create($data);
        
        if (!$donationId) {
            return false;
        }
        
        // Dapatkan data donasi lengkap
        $donation = $this->find($donationId);
        
        // Buat pembayaran di Midtrans
        $paymentInfo = $this->createMidtransPayment($donation);
        
        if (!$paymentInfo) {
            // Jika gagal membuat pembayaran, update status donasi
            $this->update($donationId, ['status' => 'failed']);
            return false;
        }
        
        // Update data pembayaran
        $this->update($donationId, [
            'payment_data' => json_encode($paymentInfo),
            'payment_channel' => $paymentInfo['payment_type'] ?? null
        ]);
        
        // Gabungkan data donasi dengan info pembayaran
        $donation['payment_info'] = $paymentInfo;
        
        return $donation;
    }
    
    /**
     * Generate ID transaksi unik
     * 
     * @return string
     */
    private function generateTransactionId() {
        $prefix = 'DH';
        $date = date('Ymd');
        $random = mt_rand(1000, 9999);
        $uniqueId = uniqid();
        
        return $prefix . $date . $random . substr($uniqueId, -4);
    }
    
    /**
     * Membuat pembayaran di Midtrans
     * 
     * @param array $donation Data donasi
     * @return array|false Data pembayaran atau false jika gagal
     */
    private function createMidtransPayment($donation) {
        // Load Midtrans Config
        \Midtrans\Config::$serverKey = MIDTRANS_SERVER_KEY;
        \Midtrans\Config::$isProduction = (MIDTRANS_ENVIRONMENT === 'production');
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
        
        // Ambil data campaign
        $campaign = (new Campaign())->find($donation['campaign_id']);
        
        if (!$campaign) {
            return false;
        }
        
        // Siapkan parameter untuk Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $donation['transaction_id'],
                'gross_amount' => (int) $donation['amount'],
            ],
            'customer_details' => [
                'first_name' => $donation['name'],
                'email' => $donation['email'],
                'phone' => $donation['phone'] ?? '',
            ],
            'item_details' => [
                [
                    'id' => 'donation-' . $donation['id'],
                    'price' => (int) $donation['amount'],
                    'quantity' => 1,
                    'name' => 'Donasi untuk ' . $campaign['title'],
                ]
            ],
            'callbacks' => [
                'finish' => BASE_URL . '/donation/finish/' . $donation['id'],
                'error' => BASE_URL . '/donation/error/' . $donation['id'],
                'pending' => BASE_URL . '/donation/pending/' . $donation['id']
            ]
        ];
        
        try {
            // Buat Snap Token
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            
            return [
                'token' => $snapToken,
                'redirect_url' => 'https://app.midtrans.com/snap/v2/vtweb/' . $snapToken,
                'payment_type' => 'snap',
            ];
        } catch (\Exception $e) {
            // Log error
            error_log('Midtrans Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update status donasi berdasarkan notifikasi Midtrans
     * 
     * @param array $notification Data notifikasi dari Midtrans
     * @return bool
     */
    public function handleMidtransNotification($notification) {
        // Verifikasi signature key
        \Midtrans\Config::$serverKey = MIDTRANS_SERVER_KEY;
        \Midtrans\Config::$isProduction = (MIDTRANS_ENVIRONMENT === 'production');
        
        try {
            $notif = new \Midtrans\Notification();
        } catch (\Exception $e) {
            return false;
        }
        
        $transactionId = $notif->order_id;
        $transactionStatus = $notif->transaction_status;
        $fraudStatus = $notif->fraud_status;
        $paymentType = $notif->payment_type;
        
        // Cari donasi berdasarkan transaction_id
        $donation = $this->findBy('transaction_id', $transactionId);
        
        if (!$donation) {
            return false;
        }
        
        // Update status berdasarkan status transaksi Midtrans
        $status = 'pending';
        
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $status = 'challenge';
            } else if ($fraudStatus == 'accept') {
                $status = 'success';
            }
        } else if ($transactionStatus == 'settlement') {
            $status = 'success';
        } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $status = 'failed';
        } else if ($transactionStatus == 'pending') {
            $status = 'pending';
        }
        
        // Update donasi
        $this->update($donation['id'], [
            'status' => $status,
            'payment_method' => $paymentType,
            'payment_data' => json_encode($notif)
        ]);
        
        // Jika donasi berhasil, update jumlah donasi pada campaign
        if ($status === 'success') {
            $campaign = new Campaign();
            $campaign->updateAmount($donation['campaign_id'], $donation['amount']);
        }
        
        return true;
    }
    
    /**
     * Mendapatkan donasi dengan detail campaign
     * 
     * @param int $id ID donasi
     * @return array|false
     */
    public function getWithDetails($id) {
        return $this->db->fetch(
            "SELECT d.*, c.title as campaign_title, c.slug as campaign_slug 
            FROM {$this->table} d
            LEFT JOIN campaigns c ON d.campaign_id = c.id
            WHERE d.id = ?",
            [$id]
        );
    }
    
    /**
     * Mendapatkan donasi berdasarkan campaign
     * 
     * @param int $campaignId ID campaign
     * @param bool $onlySuccess Hanya donasi yang berhasil
     * @param int $limit Batasan jumlah data
     * @return array
     */
    public function getByCampaign($campaignId, $onlySuccess = true, $limit = null) {
        $sql = "SELECT * FROM {$this->table} WHERE campaign_id = ?";
        $params = [$campaignId];
        
        if ($onlySuccess) {
            $sql .= " AND status = 'success'";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Mendapatkan statistik donasi
     * 
     * @return array
     */
    public function getStats() {
        // Total donasi berhasil
        $totalSuccess = $this->db->fetchColumn(
            "SELECT COALESCE(SUM(amount), 0) FROM {$this->table} WHERE status = 'success'"
        );
        
        // Jumlah donasi berhasil
        $countSuccess = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table} WHERE status = 'success'"
        );
        
        // Jumlah donatur unik
        $uniqueDonors = $this->db->fetchColumn(
            "SELECT COUNT(DISTINCT email) FROM {$this->table} WHERE status = 'success'"
        );
        
        // Donasi per metode pembayaran
        $byPaymentMethod = $this->db->fetchAll(
            "SELECT payment_method, COUNT(*) as count, SUM(amount) as total 
            FROM {$this->table} 
            WHERE status = 'success' 
            GROUP BY payment_method"
        );
        
        return [
            'total_success' => $totalSuccess,
            'count_success' => $countSuccess,
            'unique_donors' => $uniqueDonors,
            'by_payment_method' => $byPaymentMethod
        ];
    }
} 