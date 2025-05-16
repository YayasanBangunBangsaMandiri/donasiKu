<?php
namespace App\Models;

/**
 * Model untuk mengelola data donasi
 */
class Donation extends Model {
    protected $table = 'donations';
    protected $fillable = [
        'campaign_id', 'user_id', 'name', 'email', 'phone', 'amount', 
        'payment_method', 'payment_channel', 'transaction_id', 'order_id', 
        'va_number', 'payment_url', 'message', 'is_anonymous', 'status',
        'payment_expiry', 'paid_at', 'settlement_time', 'refund_time', 'notify_success'
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
    
    /**
     * Mendapatkan semua donasi untuk kampanye tertentu
     * 
     * @param int $campaignId ID kampanye
     * @param string $status Status donasi (opsional)
     * @param string $orderBy Kolom untuk pengurutan
     * @param string $order Arah pengurutan (ASC/DESC)
     * @return array
     */
    public function getAllByCampaignId($campaignId, $status = null, $orderBy = 'created_at', $order = 'DESC') {
        $sql = "SELECT * FROM {$this->table} WHERE campaign_id = ?";
        $params = [$campaignId];
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY {$orderBy} {$order}";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Mendapatkan semua donasi untuk pengguna tertentu
     * 
     * @param int $userId ID pengguna
     * @param string $status Status donasi (opsional)
     * @param string $orderBy Kolom untuk pengurutan
     * @param string $order Arah pengurutan (ASC/DESC)
     * @return array
     */
    public function getAllByUserId($userId, $status = null, $orderBy = 'created_at', $order = 'DESC') {
        $sql = "SELECT d.*, c.title as campaign_title, c.slug as campaign_slug 
                FROM {$this->table} d 
                JOIN campaigns c ON d.campaign_id = c.id
                WHERE d.user_id = ?";
        $params = [$userId];
        
        if ($status) {
            $sql .= " AND d.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY d.{$orderBy} {$order}";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Mendapatkan donasi berdasarkan ID dengan data kampanye
     * 
     * @param int $id ID donasi
     * @return array|false
     */
    public function findWithCampaign($id) {
        $sql = "SELECT d.*, c.title as campaign_title, c.slug as campaign_slug 
                FROM {$this->table} d 
                JOIN campaigns c ON d.campaign_id = c.id
                WHERE d.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Mendapatkan donasi berdasarkan order ID
     * 
     * @param string $orderId Order ID
     * @return array|false
     */
    public function findByOrderId($orderId) {
        return $this->findBy('order_id', $orderId);
    }
    
    /**
     * Mendapatkan semua donasi dengan filter
     * 
     * @param array $filters Array filter
     * @param int $page Halaman saat ini
     * @param int $perPage Jumlah data per halaman
     * @param string $orderBy Kolom untuk pengurutan
     * @param string $order Arah pengurutan (ASC/DESC)
     * @return array
     */
    public function getAll($filters = [], $page = 1, $perPage = 10, $orderBy = 'created_at', $order = 'DESC') {
        $offset = ($page - 1) * $perPage;
        $conditions = [];
        $params = [];
        
        // Building SQL conditions based on filters
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if ($value !== '' && $value !== null) {
                    switch ($key) {
                        case 'campaign_id':
                            $conditions[] = "d.campaign_id = ?";
                            $params[] = $value;
                            break;
                        case 'status':
                            $conditions[] = "d.status = ?";
                            $params[] = $value;
                            break;
                        case 'payment_method':
                            $conditions[] = "d.payment_method = ?";
                            $params[] = $value;
                            break;
                        case 'payment_channel':
                            $conditions[] = "d.payment_channel = ?";
                            $params[] = $value;
                            break;
                        case 'date_from':
                            $conditions[] = "DATE(d.created_at) >= ?";
                            $params[] = $value;
                            break;
                        case 'date_to':
                            $conditions[] = "DATE(d.created_at) <= ?";
                            $params[] = $value;
                            break;
                        case 'amount_min':
                            $conditions[] = "d.amount >= ?";
                            $params[] = $value;
                            break;
                        case 'amount_max':
                            $conditions[] = "d.amount <= ?";
                            $params[] = $value;
                            break;
                        case 'search':
                            $conditions[] = "(d.name LIKE ? OR d.email LIKE ? OR d.transaction_id LIKE ? OR d.order_id LIKE ?)";
                            $searchTerm = '%' . $value . '%';
                            $params[] = $searchTerm;
                            $params[] = $searchTerm;
                            $params[] = $searchTerm;
                            $params[] = $searchTerm;
                            break;
                    }
                }
            }
        }
        
        // Base SQL query
        $sql = "SELECT d.*, c.title as campaign_title, c.slug as campaign_slug 
                FROM {$this->table} d 
                JOIN campaigns c ON d.campaign_id = c.id";
        
        // Add conditions if any
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        // Add ordering
        $sql .= " ORDER BY d.{$orderBy} {$order}";
        
        // Add pagination
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        
        // Get data
        $donations = $this->db->fetchAll($sql, $params);
        
        // Count total records for pagination
        $countSql = "SELECT COUNT(*) FROM {$this->table} d JOIN campaigns c ON d.campaign_id = c.id";
        if (!empty($conditions)) {
            $countSql .= " WHERE " . implode(" AND ", $conditions);
        }
        $total = $this->db->fetchColumn($countSql, $params);
        
        // Calculate pagination info
        $lastPage = ceil($total / $perPage);
        
        return [
            'data' => $donations,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage
        ];
    }
    
    /**
     * Mendapatkan statistik donasi
     * 
     * @param string $period Periode (daily, monthly, yearly)
     * @param string $dateFrom Tanggal awal
     * @param string $dateTo Tanggal akhir
     * @return array
     */
    public function getStatistics($period = 'monthly', $dateFrom = null, $dateTo = null) {
        // Default date range: last 30 days
        if (!$dateFrom) {
            $dateFrom = date('Y-m-d', strtotime('-30 days'));
        }
        
        if (!$dateTo) {
            $dateTo = date('Y-m-d');
        }
        
        // Format untuk SQL GROUP BY
        $dateFormat = '';
        $labelFormat = '';
        
        switch ($period) {
            case 'daily':
                $dateFormat = '%Y-%m-%d';
                $labelFormat = 'd M';
                break;
            case 'monthly':
                $dateFormat = '%Y-%m';
                $labelFormat = 'M Y';
                break;
            case 'yearly':
                $dateFormat = '%Y';
                $labelFormat = 'Y';
                break;
            default:
                $dateFormat = '%Y-%m-%d';
                $labelFormat = 'd M';
        }
        
        // SQL query untuk donasi sukses berdasarkan periode
        $sql = "SELECT 
                DATE_FORMAT(created_at, '{$dateFormat}') as period,
                COUNT(*) as total_donations,
                SUM(amount) as total_amount,
                payment_method,
                COUNT(DISTINCT email) as unique_donors
                FROM {$this->table}
                WHERE status = 'success'
                AND DATE(created_at) BETWEEN ? AND ?
                GROUP BY period, payment_method
                ORDER BY period";
        
        $statistics = $this->db->fetchAll($sql, [$dateFrom, $dateTo]);
        
        // Format untuk output
        $result = [
            'labels' => [],
            'datasets' => [
                'total_amount' => [],
                'total_donations' => [],
                'payment_methods' => []
            ],
            'summary' => [
                'total_amount' => 0,
                'total_donations' => 0,
                'unique_donors' => 0,
                'avg_donation' => 0
            ]
        ];
        
        // Reshape data for charts
        foreach ($statistics as $stat) {
            // Add period to labels if not already added
            if (!in_array($stat['period'], $result['labels'])) {
                $result['labels'][] = $stat['period'];
                $result['datasets']['total_amount'][$stat['period']] = 0;
                $result['datasets']['total_donations'][$stat['period']] = 0;
            }
            
            // Update datasets
            $result['datasets']['total_amount'][$stat['period']] += $stat['total_amount'];
            $result['datasets']['total_donations'][$stat['period']] += $stat['total_donations'];
            
            // Update payment methods
            if (!isset($result['datasets']['payment_methods'][$stat['payment_method']])) {
                $result['datasets']['payment_methods'][$stat['payment_method']] = 0;
            }
            $result['datasets']['payment_methods'][$stat['payment_method']] += $stat['total_amount'];
            
            // Update summary
            $result['summary']['total_amount'] += $stat['total_amount'];
            $result['summary']['total_donations'] += $stat['total_donations'];
            $result['summary']['unique_donors'] = max($result['summary']['unique_donors'], $stat['unique_donors']);
        }
        
        // Calculate average donation
        if ($result['summary']['total_donations'] > 0) {
            $result['summary']['avg_donation'] = $result['summary']['total_amount'] / $result['summary']['total_donations'];
        }
        
        // Format labels based on period
        $formattedLabels = [];
        foreach ($result['labels'] as $label) {
            switch ($period) {
                case 'daily':
                    $date = \DateTime::createFromFormat('Y-m-d', $label);
                    $formattedLabels[] = $date ? $date->format('d M') : $label;
                    break;
                case 'monthly':
                    $date = \DateTime::createFromFormat('Y-m', $label);
                    $formattedLabels[] = $date ? $date->format('M Y') : $label;
                    break;
                case 'yearly':
                    $formattedLabels[] = $label;
                    break;
                default:
                    $formattedLabels[] = $label;
            }
        }
        
        $result['formatted_labels'] = $formattedLabels;
        
        return $result;
    }
    
    /**
     * Mengupdate status donasi yang kedaluwarsa
     * 
     * @return int Jumlah donasi yang diupdate
     */
    public function updateExpiredDonations() {
        $now = date('Y-m-d H:i:s');
        
        $sql = "UPDATE {$this->table} 
                SET status = 'expired' 
                WHERE status = 'pending' 
                AND payment_expiry IS NOT NULL 
                AND payment_expiry < ?";
        
        $stmt = $this->db->query($sql, [$now]);
        
        return $stmt->rowCount();
    }
} 